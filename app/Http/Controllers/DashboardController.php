<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\ApoyoPadres;
use App\Models\AsistenciaAula;
use App\Models\AsistenciaPersonal;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;
use App\Models\SolicitudEdicionNota;
use App\Models\Usuario;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            /** @var Usuario $user */
            $user = Auth::user();
            $docente = Docente::where('usuario_id', $user->id)->first();
            $modalidades = Modalidad::orderBy('id')->get();

            $queryAvisos = DB::table('aviso')
                ->join('usuario', 'aviso.autor_id', '=', 'usuario.id')
                ->select('aviso.*', 'usuario.nombre_completo as autor_nombre')
                ->where('aviso.activo', true);

            $avisos = $this->filtrarAvisosPorScope($queryAvisos, $user, $docente)
                ->orderByDesc('aviso.created_at')
                ->take(5)
                ->get();

            $totalMatriculados = 0;
            $totalPersonal = 0;
            $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];
            $diaDisplay = array_combine($diasBD, $diasSemana);
            $dbMetricas = [];
            $muralData = [];
            $aulaGuia = null;
            $esDocenteGuia = false;
            $bloques = collect();
            $matrizHorario = [];
            $esquemaActivo = 'Regular';

            $anioActivo = AnioEscolar::where('estado', 'activo')->first();
            $rolesDocentes = [
                'docente_guia',
                'docente_asignatura',
                'docente_auxiliar',
                'docente_suplente',
                'Docente Guia',
                'Docente por Asignatura',
                'Docente Auxiliar',
                'Docente Suplente',
            ];

            $queryDocentes = Usuario::whereHas('roles', function ($q) use ($rolesDocentes) {
                $q->whereIn('name', $rolesDocentes);
            });

            if ($user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
                $totalMatriculados = Matricula::where('estado', 'activo')
                    ->when($anioActivo, fn ($q) => $q->where('anio_escolar_id', $anioActivo->id))
                    ->count();
                $totalPersonal = (clone $queryDocentes)->count();
                $dbMetricas = $this->calcularMetricas();
                $muralData = $this->generarMuralDigital($anioActivo, $request->query('modalidad_mural') ? (int) $request->query('modalidad_mural') : null);
            } elseif ($user->hasAnyRole(['gestor_it', 'secretaria', 'psicologa', 'Gestor de Usuarios', 'Secretaria', 'Psicóloga'])) {
                $totalMatriculados = Matricula::where('estado', 'activo')
                    ->when($anioActivo, fn ($q) => $q->where('anio_escolar_id', $anioActivo->id))
                    ->count();
                $totalPersonal = (clone $queryDocentes)->count();
            } elseif ($user->hasAnyRole(['coordinador', 'Coordinador']) && $docente && $docente->modalidad_coordina_id && $anioActivo) {
                $totalMatriculados = Matricula::where('estado', 'activo')
                    ->whereHas('aula', fn ($q) => $q->where('modalidad_id', $docente->modalidad_coordina_id))
                    ->where('anio_escolar_id', $anioActivo->id)
                    ->count();
                $totalPersonal = (clone $queryDocentes)
                    ->whereHas('docente', fn ($q) => $q->where('modalidad_id', $docente->modalidad_coordina_id))
                    ->count();
                $dbMetricas = $this->calcularMetricasPorModalidad((int) $docente->modalidad_coordina_id, $anioActivo);
                $muralData = $this->generarMuralDigital($anioActivo, (int) $docente->modalidad_coordina_id);
            }

            if ($docente && $anioActivo) {
                $aulaGuia = Aula::with('grado')
                    ->where('docente_guia_id', $docente->id)
                    ->where('anio_escolar_id', $anioActivo->id)
                    ->first();

                $esDocenteGuia = (bool) $aulaGuia;

                $horariosRaw = Horario::with([
                    'bloqueHorario',
                    'aulaAsignaturaDocente.asignatura',
                    'aulaAsignaturaDocente.aula.grado',
                    'aulaAsignaturaDocente.aula.modalidad',
                ])
                    ->whereHas('aulaAsignaturaDocente', function ($q) use ($docente, $anioActivo) {
                        $q->where('docente_id', $docente->id)
                            ->where('anio_escolar_id', $anioActivo->id)
                            ->where('activo', true);
                    })
                    ->get();

                $bloques = $horariosRaw->pluck('bloqueHorario')->filter()->unique('hora_inicio')->sortBy('hora_inicio')->values();
                if ($bloques->count() > 0) {
                    $esquemaActivo = $bloques->first()->tipo_jornada ?? 'Regular';
                }

                foreach ($horariosRaw as $horario) {
                    if (!$horario->bloqueHorario) {
                        continue;
                    }
                    $dia = $diaDisplay[$horario->dia_semana] ?? $horario->dia_semana;
                    $hora = $horario->bloqueHorario->hora_inicio;

                    $matrizHorario[$dia][$hora] = [
                        'asignacion_id' => $horario->aula_asignatura_docente_id,
                        'asignatura' => $horario->aulaAsignaturaDocente->asignatura->nombre,
                        'aula' => ($horario->aulaAsignaturaDocente->aula->grado->nombre ?? '') . ' - ' . ($horario->aulaAsignaturaDocente->aula->nombre ?? ''),
                        'hora_inicio' => $horario->bloqueHorario->hora_inicio,
                        'hora_fin' => $horario->bloqueHorario->hora_fin,
                        'modalidad_id' => $horario->aulaAsignaturaDocente->aula->modalidad_id,
                    ];
                }
            }

            $docentesSinMarcar = collect();
            $solicitudesPendientes = collect();
            $asistenciaSemanal = null;
            $rendimientoAula = null;
            $muralAula = [];
            for ($m = 1; $m <= 12; $m++) {
                $muralAula[$m] = ['asistencia_est' => null, 'asistencia_doc' => null];
            }
            $rendimientoCortes = [];

            if ($user->hasAnyRole(['coordinador', 'Coordinador'])) {
                $hoy = now()->timezone('America/Managua')->toDateString();
                $docentesIds = (clone $queryDocentes)
                    ->whereHas('docente', fn ($q) => $q->where('modalidad_id', $docente?->modalidad_coordina_id))
                    ->pluck('id');
                $docentesQueMarcaron = AsistenciaPersonal::where('fecha', $hoy)->whereIn('usuario_id', $docentesIds)->pluck('usuario_id');

                $docentesSinMarcar = (clone $queryDocentes)
                    ->whereNotIn('id', $docentesQueMarcaron)
                    ->whereHas('docente', fn ($q) => $q->where('modalidad_id', $docente?->modalidad_coordina_id))
                    ->get();

                $solicitudesPendientes = SolicitudEdicionNota::with(['docente.usuario', 'nota.aulaAsignaturaDocente.asignatura'])
                    ->where('estado', 'Pendiente')
                    ->whereHas('nota.aulaAsignaturaDocente.aula', fn ($q) => $q->where('modalidad_id', $docente?->modalidad_coordina_id))
                    ->orderByDesc('created_at')
                    ->get();
            } elseif ($user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
                $hoy = now()->timezone('America/Managua')->toDateString();
                $docentesIds = (clone $queryDocentes)->pluck('id');
                $docentesQueMarcaron = AsistenciaPersonal::where('fecha', $hoy)->whereIn('usuario_id', $docentesIds)->pluck('usuario_id');

                $docentesSinMarcar = (clone $queryDocentes)
                    ->whereNotIn('id', $docentesQueMarcaron)
                    ->whereHas('docente')
                    ->get();

                $solicitudesPendientes = SolicitudEdicionNota::with(['docente.usuario', 'nota.aulaAsignaturaDocente.asignatura'])
                    ->where('estado', 'Pendiente')
                    ->orderByDesc('created_at')
                    ->get();
            }

            if ($aulaGuia) {
                $matriculaIds = Matricula::where('aula_id', $aulaGuia->id)->where('estado', 'activo')->pluck('id');
                $inicioSemana = now()->timezone('America/Managua')->startOfWeek();
                $finSemana = now()->timezone('America/Managua')->endOfWeek();

                $asistencias = AsistenciaAula::whereIn('matricula_id', $matriculaIds)
                    ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
                    ->get();

                $totalRegistros = $asistencias->count();
                $presentes = $asistencias->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();

                $asistenciaSemanal = [
                    'porcentaje' => $totalRegistros > 0 ? round(($presentes / $totalRegistros) * 100, 1) : 0,
                    'total_matriculas' => $matriculaIds->count(),
                ];

                $rendimientoAula = app(ReporteService::class)->rendimientoAula($aulaGuia);

                $exprMes = DB::getDriverName() === 'sqlite'
                    ? "CAST(strftime('%m', fecha) AS INTEGER)"
                    : 'MONTH(fecha)';

                $anioFiltro = $anioActivo?->fecha_inicio?->year ?? now()->year;

                $astEst = DB::table('asistencia_aula')
                    ->whereIn('matricula_id', $matriculaIds)
                    ->whereYear('fecha', $anioFiltro)
                    ->selectRaw($exprMes . ' as mes, COUNT(CASE WHEN estado_asistencia IN (\'Presente\', \'Actividad Institucional\') THEN 1 END) as presentes, COUNT(*) as total')
                    ->groupBy('mes')
                    ->get()
                    ->keyBy('mes');

                $astDoc = DB::table('asistencia_personal')
                    ->where('usuario_id', $user->id)
                    ->whereYear('fecha', $anioFiltro)
                    ->selectRaw($exprMes . ' as mes, COUNT(CASE WHEN estado = \'Presente\' THEN 1 END) as presentes, COUNT(*) as total')
                    ->groupBy('mes')
                    ->get()
                    ->keyBy('mes');

                $cortes = CorteEvaluativo::orderBy('numero')->get();
                foreach ($cortes as $corte) {
                    $notasCorte = Nota::whereIn('matricula_id', $matriculaIds)
                        ->where('corte_evaluativo_id', $corte->id)
                        ->get();

                    if ($notasCorte->count() > 0 && $matriculaIds->count() > 0) {
                        $aplazados = $notasCorte->where('nota_cuantitativa', '<', 60)->groupBy('matricula_id')->count();
                        $aprobadosLimpios = $matriculaIds->count() - $aplazados;
                        $rendimientoCortes[$corte->numero] = [
                            'promedio' => round((float) $notasCorte->avg('nota_cuantitativa'), 1),
                            'indice' => round(($aprobadosLimpios / $matriculaIds->count()) * 100, 1),
                        ];
                    }
                }

                for ($m = 1; $m <= 12; $m++) {
                    $muralAula[$m] = [
                        'asistencia_est' => isset($astEst[$m]) && $astEst[$m]->total > 0 ? round(($astEst[$m]->presentes / $astEst[$m]->total) * 100, 1) : null,
                        'asistencia_doc' => isset($astDoc[$m]) && $astDoc[$m]->total > 0 ? round(($astDoc[$m]->presentes / $astDoc[$m]->total) * 100, 1) : null,
                    ];
                }
            }

            return view('dashboard', compact(
                'avisos',
                'modalidades',
                'totalMatriculados',
                'totalPersonal',
                'diasSemana',
                'dbMetricas',
                'muralData',
                'aulaGuia',
                'esDocenteGuia',
                'bloques',
                'matrizHorario',
                'esquemaActivo',
                'docentesSinMarcar',
                'solicitudesPendientes',
                'asistenciaSemanal',
                'rendimientoAula',
                'muralAula',
                'rendimientoCortes'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return view('dashboard', [
                'avisos' => collect(),
                'modalidades' => collect(),
                'totalMatriculados' => 0,
                'totalPersonal' => 0,
                'diasSemana' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
                'dbMetricas' => [],
                'muralData' => [],
                'aulaGuia' => null,
                'esDocenteGuia' => false,
                'bloques' => collect(),
                'matrizHorario' => [],
                'esquemaActivo' => 'Regular',
                'docentesSinMarcar' => collect(),
                'solicitudesPendientes' => collect(),
                'asistenciaSemanal' => null,
                'rendimientoAula' => null,
                'muralAula' => [],
                'rendimientoCortes' => [],
            ])->with('error', $this->formatearError($e, 'Ocurrió un problema técnico al cargar algunos datos del panel.'));
        }
    }

    protected function filtrarAvisosPorScope($query, Usuario $user, ?Docente $docente)
    {
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            $modalidadesPermitidas = [];

            if ($user->hasAnyRole(['coordinador', 'Coordinador']) && $docente) {
                $modalidadesPermitidas[] = $docente->modalidad_coordina_id;
            }

            if ($user->hasAnyRole(['docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'Docente Guia', 'Docente por Asignatura']) && $docente) {
                $modalidadesClase = AulaAsignaturaDocente::where('docente_id', $docente->id)
                    ->where('activo', true)
                    ->join('aula', 'aula_asignatura_docente.aula_id', '=', 'aula.id')
                    ->pluck('aula.modalidad_id')
                    ->all();

                $modalidadesPermitidas = array_merge($modalidadesPermitidas, $modalidadesClase);
            }

            $modalidadesPermitidas = array_values(array_filter(array_unique($modalidadesPermitidas)));

            $query->where(function ($q) use ($modalidadesPermitidas) {
                $q->whereNull('aviso.modalidad_id');
                if ($modalidadesPermitidas !== []) {
                    $q->orWhereIn('aviso.modalidad_id', $modalidadesPermitidas);
                }
            });
        }

        return $query;
    }

    protected function generarMuralDigital(?AnioEscolar $anioActivo, ?int $modalidadFiltro = null): array
    {
        if (!$anioActivo) {
            return [];
        }

        $mural = [
            'asistencia_estudiantes' => [],
            'asistencia_docentes' => [],
            'padres' => [],
            'avance_contenido' => [],
            'aprobados_cortes' => [],
            'promedio_cortes' => [],
        ];

        try {
            $aulasQuery = Aula::where('anio_escolar_id', $anioActivo->id);
            if ($modalidadFiltro) {
                $aulasQuery->where('modalidad_id', $modalidadFiltro);
            }
            $aulas = $aulasQuery->pluck('id');
            $matriculas = Matricula::whereIn('aula_id', $aulas)->pluck('id');

            $asistenciasAlumnos = AsistenciaAula::whereIn('matricula_id', $matriculas)->get();
            foreach ($asistenciasAlumnos->groupBy(fn ($item) => Carbon::parse($item->fecha)->month) as $mes => $registros) {
                $total = $registros->count();
                $presentes = $registros->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
                $mural['asistencia_estudiantes'][$mes] = $total > 0 ? (int) round(($presentes / $total) * 100) : 0;
            }

            $docentesQuery = Docente::query();
            if ($modalidadFiltro) {
                $docentesQuery->where('modalidad_id', $modalidadFiltro);
            }
            $usuariosDocentes = $docentesQuery->pluck('usuario_id');
            $fechaInicio = $anioActivo->fecha_inicio ?? now()->startOfYear()->toDateString();
            $fechaFin = $anioActivo->fecha_fin ?? now()->endOfYear()->toDateString();

            $asistenciasDocentes = AsistenciaPersonal::whereIn('usuario_id', $usuariosDocentes)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->get();

            foreach ($asistenciasDocentes->groupBy(fn ($item) => Carbon::parse($item->fecha)->month) as $mes => $registros) {
                $total = $registros->count();
                $asistencias = $registros->whereIn('estado', ['Presente', 'Retardo'])->count();
                $mural['asistencia_docentes'][$mes] = $total > 0 ? (int) round(($asistencias / $total) * 100) : 0;
            }

            $cortes = CorteEvaluativo::all();
            $mesesCortes = [1 => 6, 2 => 9, 3 => 11];
            $notasGlobales = Nota::whereIn('matricula_id', $matriculas)->get();

            foreach ($cortes as $corte) {
                $notasCorte = $notasGlobales->where('corte_evaluativo_id', $corte->id);
                $mes = $mesesCortes[$corte->numero] ?? ($corte->numero + 5);

                if ($notasCorte->count() > 0) {
                    $mural['promedio_cortes'][$mes] = round((float) $notasCorte->avg('nota_cuantitativa'), 1);
                    $agrupadoAlumnos = $notasCorte->groupBy('matricula_id');
                    $limpios = 0;
                    foreach ($agrupadoAlumnos as $notasAlumno) {
                        if ($notasAlumno->where('nota_cuantitativa', '<', 60)->count() === 0) {
                            $limpios++;
                        }
                    }
                    $mural['aprobados_cortes'][$mes] = $agrupadoAlumnos->count() > 0 ? (int) round(($limpios / $agrupadoAlumnos->count()) * 100) : 0;
                }
            }

            $apoyoPadres = ApoyoPadres::whereIn('aula_id', $aulas)->get();
            foreach ($apoyoPadres->groupBy(fn ($item) => $item->created_at ? $item->created_at->month : now()->month) as $mes => $registros) {
                $total = $registros->sum('total_padres');
                $apoyan = $registros->sum('cantidad_apoyan');
                if ($total > 0) {
                    $mural['padres'][$mes] = (int) round(($apoyan / $total) * 100);
                }
            }

            $asignaciones = AulaAsignaturaDocente::whereIn('aula_id', $aulas)->pluck('id');
            $avances = \App\Models\AvanceContenido::whereIn('aula_asignatura_docente_id', $asignaciones)->get();
            foreach ($avances->groupBy(fn ($item) => $item->created_at ? $item->created_at->month : now()->month) as $mes => $registros) {
                $mural['avance_contenido'][$mes] = (int) round((float) $registros->avg('porcentaje_avance'));
            }
        } catch (\Throwable $e) {
            Log::warning('Error generando Mural Digital: ' . $e->getMessage());
        }

        return $mural;
    }

    protected function calcularMetricas(): array
    {
        try {
            $anioActivo = AnioEscolar::where('estado', 'activo')->first();
            if (!$anioActivo) {
                return [];
            }

            $modalidades = Modalidad::orderBy('id')->get();
            $matriculados = $asistenciaAlumnos = $asistenciaDocentes = $rendimiento = [];
            $apoyoPadres = $aprobados = $reprobadosLeves = $reprobadosGraves = [];
            $promedioNotas = $avances = $retencion = $puntualidad = [];

            foreach ($modalidades as $modalidad) {
                $aulaIds = Aula::where('modalidad_id', $modalidad->id)->where('anio_escolar_id', $anioActivo->id)->pluck('id');
                $matriculaQuery = Matricula::whereIn('aula_id', $aulaIds);
                $activos = (clone $matriculaQuery)->where('estado', 'activo')->count();
                $retirados = (clone $matriculaQuery)->where('estado', 'retirado')->count();

                $matriculados[] = $activos;
                $retencion[] = ($activos + $retirados) > 0 ? round(($activos / ($activos + $retirados)) * 100, 1) : 0;

                $matriculaIds = (clone $matriculaQuery)->where('estado', 'activo')->pluck('id');
                $asistAula = AsistenciaAula::whereIn('matricula_id', $matriculaIds)->get();
                $asistenciaAlumnos[] = $asistAula->count() > 0
                    ? round(($asistAula->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count() / $asistAula->count()) * 100, 1)
                    : 0;

                $notasModalidad = Nota::whereIn('matricula_id', $matriculaIds)->get();
                $rendimiento[] = $notasModalidad->count() > 0
                    ? round(($notasModalidad->where('nota_cuantitativa', '>=', 60)->count() / $notasModalidad->count()) * 100, 1)
                    : 0;
                $promedioNotas[] = $notasModalidad->count() > 0 ? round((float) $notasModalidad->avg('nota_cuantitativa'), 2) : 0;

                $alumnosNotas = $notasModalidad->groupBy('matricula_id');
                $limpios = $leves = $graves = 0;
                $totalAlumnosEval = $alumnosNotas->count();
                foreach ($alumnosNotas as $notasAlumno) {
                    $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();
                    if ($reprobadas === 0) {
                        $limpios++;
                    } elseif ($reprobadas <= 2) {
                        $leves++;
                    } else {
                        $graves++;
                    }
                }

                $aprobados[] = $totalAlumnosEval > 0 ? round(($limpios / $totalAlumnosEval) * 100, 1) : 0;
                $reprobadosLeves[] = $totalAlumnosEval > 0 ? round(($leves / $totalAlumnosEval) * 100, 1) : 0;
                $reprobadosGraves[] = $totalAlumnosEval > 0 ? round(($graves / $totalAlumnosEval) * 100, 1) : 0;

                $apoyo = ApoyoPadres::whereIn('aula_id', $aulaIds)->get();
                $apoyoPadres[] = $apoyo->sum('total_padres') > 0 ? round(($apoyo->sum('cantidad_apoyan') / $apoyo->sum('total_padres')) * 100, 1) : 0;

                $asignacionIds = AulaAsignaturaDocente::whereIn('aula_id', $aulaIds)->pluck('id');
                $avancesMod = \App\Models\AvanceContenido::whereIn('aula_asignatura_docente_id', $asignacionIds)->get();
                $avances[] = $avancesMod->count() > 0 ? round((float) $avancesMod->avg('porcentaje_avance'), 1) : 0;

                $docentesIds = Docente::where('modalidad_id', $modalidad->id)->pluck('usuario_id');
                $fechaInicio = $anioActivo->fecha_inicio ?? now()->startOfYear()->toDateString();
                $fechaFin = $anioActivo->fecha_fin ?? now()->endOfYear()->toDateString();
                $asisPersonal = AsistenciaPersonal::whereIn('usuario_id', $docentesIds)->whereBetween('fecha', [$fechaInicio, $fechaFin])->get();
                $totPersonal = $asisPersonal->count();

                $asistenciaDocentes[] = $totPersonal > 0 ? round(($asisPersonal->whereIn('estado', ['Presente', 'Retardo'])->count() / $totPersonal) * 100, 1) : 0;
                $puntualidad[] = $totPersonal > 0 ? round(($asisPersonal->where('estado', 'Presente')->count() / $totPersonal) * 100, 1) : 0;
            }

            return [
                'matriculados' => ['titulo' => 'Alumnos Matriculados Activos', 'datos' => $matriculados],
                'asistencia_alumnos' => ['titulo' => 'Asistencia de Alumnos (%)', 'datos' => $asistenciaAlumnos],
                'asistencia_docentes' => ['titulo' => 'Asistencia de Docentes (%)', 'datos' => $asistenciaDocentes],
                'rendimiento_modalidad' => ['titulo' => 'Rendimiento Académico General (%)', 'datos' => $rendimiento],
                'apoyo_padres' => ['titulo' => 'Participación de Padres (%)', 'datos' => $apoyoPadres],
                'aprobados' => ['titulo' => 'Alumnos Aprobados Limpios (%)', 'datos' => $aprobados],
                'reprobados_leves' => ['titulo' => 'Reprobados (1 a 2 Clases) (%)', 'datos' => $reprobadosLeves],
                'reprobados_graves' => ['titulo' => 'Reprobados Críticos (3+ Clases) (%)', 'datos' => $reprobadosGraves],
                'promedio_notas' => ['titulo' => 'Promedio de Calificaciones (Escala 0-100)', 'datos' => $promedioNotas],
                'avances_silabo' => ['titulo' => 'Avance Curricular del Maestro (%)', 'datos' => $avances],
                'retencion' => ['titulo' => 'Retención Estudiantil (%)', 'datos' => $retencion],
                'puntualidad' => ['titulo' => 'Puntualidad en Horario de Entrada (%)', 'datos' => $puntualidad],
            ];
        } catch (\Throwable $e) {
            Log::warning('Error al calcular métricas globales del dashboard: ' . $e->getMessage());

            return [];
        }
    }

    protected function calcularMetricasPorModalidad(int $modalidadId, ?AnioEscolar $anioActivo): array
    {
        if (!$anioActivo || !Modalidad::find($modalidadId)) {
            return [];
        }

        try {
            $aulaIds = Aula::where('modalidad_id', $modalidadId)->where('anio_escolar_id', $anioActivo->id)->pluck('id');
            $matriculaQuery = Matricula::whereIn('aula_id', $aulaIds);
            $activos = (clone $matriculaQuery)->where('estado', 'activo')->count();
            $retirados = (clone $matriculaQuery)->where('estado', 'retirado')->count();
            $retVal = ($activos + $retirados) > 0 ? round(($activos / ($activos + $retirados)) * 100, 1) : 0;

            $matriculaIds = (clone $matriculaQuery)->where('estado', 'activo')->pluck('id');
            $asistAula = AsistenciaAula::whereIn('matricula_id', $matriculaIds)->get();
            $asistVal = $asistAula->count() > 0
                ? round(($asistAula->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count() / $asistAula->count()) * 100, 1)
                : 0;

            $notasModalidad = Nota::whereIn('matricula_id', $matriculaIds)->get();
            $rendVal = $notasModalidad->count() > 0
                ? round(($notasModalidad->where('nota_cuantitativa', '>=', 60)->count() / $notasModalidad->count()) * 100, 1)
                : 0;
            $promVal = $notasModalidad->count() > 0 ? round((float) $notasModalidad->avg('nota_cuantitativa'), 2) : 0;

            $alumnosNotas = $notasModalidad->groupBy('matricula_id');
            $limpios = $leves = $graves = 0;
            $totalAlumnosEval = $alumnosNotas->count();
            foreach ($alumnosNotas as $notasAlumno) {
                $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();
                if ($reprobadas === 0) {
                    $limpios++;
                } elseif ($reprobadas <= 2) {
                    $leves++;
                } else {
                    $graves++;
                }
            }

            $aprobVal = $totalAlumnosEval > 0 ? round(($limpios / $totalAlumnosEval) * 100, 1) : 0;
            $levesVal = $totalAlumnosEval > 0 ? round(($leves / $totalAlumnosEval) * 100, 1) : 0;
            $gravesVal = $totalAlumnosEval > 0 ? round(($graves / $totalAlumnosEval) * 100, 1) : 0;

            $apoyo = ApoyoPadres::whereIn('aula_id', $aulaIds)->get();
            $apoyoVal = $apoyo->sum('total_padres') > 0 ? round(($apoyo->sum('cantidad_apoyan') / $apoyo->sum('total_padres')) * 100, 1) : 0;

            $asignacionIds = AulaAsignaturaDocente::whereIn('aula_id', $aulaIds)->pluck('id');
            $avancesMod = \App\Models\AvanceContenido::whereIn('aula_asignatura_docente_id', $asignacionIds)->get();
            $avanceVal = $avancesMod->count() > 0 ? round((float) $avancesMod->avg('porcentaje_avance'), 1) : 0;

            $docentesIds = Docente::where('modalidad_id', $modalidadId)->pluck('usuario_id');
            $fechaInicio = $anioActivo->fecha_inicio ?? now()->startOfYear()->toDateString();
            $fechaFin = $anioActivo->fecha_fin ?? now()->endOfYear()->toDateString();
            $asisPersonal = AsistenciaPersonal::whereIn('usuario_id', $docentesIds)->whereBetween('fecha', [$fechaInicio, $fechaFin])->get();
            $totPersonal = $asisPersonal->count();

            $asistDocVal = $totPersonal > 0 ? round(($asisPersonal->whereIn('estado', ['Presente', 'Retardo'])->count() / $totPersonal) * 100, 1) : 0;
            $puntVal = $totPersonal > 0 ? round(($asisPersonal->where('estado', 'Presente')->count() / $totPersonal) * 100, 1) : 0;

            return [
                'matriculados' => ['titulo' => 'Alumnos Matriculados Activos', 'datos' => [$activos]],
                'asistencia_alumnos' => ['titulo' => 'Asistencia de Alumnos (%)', 'datos' => [$asistVal]],
                'asistencia_docentes' => ['titulo' => 'Asistencia de Docentes (%)', 'datos' => [$asistDocVal]],
                'rendimiento_modalidad' => ['titulo' => 'Rendimiento Académico General (%)', 'datos' => [$rendVal]],
                'apoyo_padres' => ['titulo' => 'Participación de Padres (%)', 'datos' => [$apoyoVal]],
                'aprobados' => ['titulo' => 'Alumnos Aprobados Limpios (%)', 'datos' => [$aprobVal]],
                'reprobados_leves' => ['titulo' => 'Reprobados (1 a 2 Clases) (%)', 'datos' => [$levesVal]],
                'reprobados_graves' => ['titulo' => 'Reprobados Críticos (3+ Clases) (%)', 'datos' => [$gravesVal]],
                'promedio_notas' => ['titulo' => 'Promedio de Calificaciones (Escala 0-100)', 'datos' => [$promVal]],
                'avances_silabo' => ['titulo' => 'Avance Curricular del Maestro (%)', 'datos' => [$avanceVal]],
                'retencion' => ['titulo' => 'Retención Estudiantil (%)', 'datos' => [$retVal]],
                'puntualidad' => ['titulo' => 'Puntualidad en Horario de Entrada (%)', 'datos' => [$puntVal]],
            ];
        } catch (\Throwable $e) {
            Log::warning('Error al calcular métricas por modalidad: ' . $e->getMessage());

            return [];
        }
    }

    public function storeAviso(Request $request)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'coordinador', 'Director', 'Subdirector', 'Coordinador'])) {
            return back()->with('error', 'No tienes permiso para publicar avisos.');
        }

        $request->validate([
            'titulo' => ['required', 'string', 'max:120'],
            'mensaje' => ['required', 'string', 'max:1000'],
            'modalidad_id' => ['nullable', 'integer', 'exists:modalidad,id'],
        ]);

        try {
            $modalidadId = $request->modalidad_id;
            if ($user->hasAnyRole(['coordinador', 'Coordinador']) && !$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
                $docente = Docente::where('usuario_id', $user->id)->first();
                $modalidadId = $docente?->modalidad_coordina_id;
            }

            DB::table('aviso')->insert([
                'titulo' => $request->titulo,
                'mensaje' => $request->mensaje,
                'autor_id' => $user->id,
                'modalidad_id' => $modalidadId,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('dashboard')->with('success', 'Aviso publicado correctamente.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un error al intentar publicar el aviso.'));
        }
    }

    public function updateAviso(Request $request, $id)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            return back()->with('error', 'No tienes permiso para editar avisos.');
        }

        $request->validate([
            'titulo' => ['required', 'string', 'max:120'],
            'mensaje' => ['required', 'string', 'max:1000'],
        ]);

        try {
            DB::table('aviso')->where('id', $id)->update([
                'titulo' => $request->titulo,
                'mensaje' => $request->mensaje,
                'updated_at' => now(),
            ]);

            return redirect()->route('dashboard')->with('success', 'Aviso actualizado correctamente.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un problema al actualizar el aviso.'));
        }
    }

    public function destroyAviso($id)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            return back()->with('error', 'No tienes permiso para eliminar avisos.');
        }

        try {
            DB::table('aviso')->where('id', $id)->delete();

            return redirect()->route('dashboard')->with('success', 'Aviso eliminado del sistema.');
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el aviso.'));
        }
    }
}