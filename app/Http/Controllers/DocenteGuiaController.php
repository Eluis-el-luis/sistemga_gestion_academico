<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\BloqueHorario;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Nota;
use App\Models\Usuario;
use App\Services\ReporteService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocenteGuiaController extends Controller
{
    public function misAlumnos()
    {
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $docente = Docente::where('usuario_id', $usuario->id)->firstOrFail();

            $aula = Aula::with('grado')->where('docente_guia_id', $docente->id)->first();
            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como Maestro Guía.');
            }

            $alumnos = Alumno::whereHas('matriculas', fn ($q) => $q->where('aula_id', $aula->id)->where('estado', 'activo'))
                ->with(['matriculas' => fn ($q) => $q->where('aula_id', $aula->id)->where('estado', 'activo')->with('notas')])
                ->get();

            $totalAlumnos = $alumnos->count();
            $aprobadosLimpios = $enReparacion = $riesgoCritico = 0;

            foreach ($alumnos as $alumno) {
                $matricula = $alumno->matriculas->first();
                $notas = $matricula ? $matricula->notas : collect();

                if ($notas->count() > 0) {
                    $alumno->promedio_global = round((float) $notas->avg('nota_cuantitativa'), 2);
                    $alumno->clases_reprobadas = $notas->where('nota_cuantitativa', '<', 60)->count();
                } else {
                    $alumno->promedio_global = 0;
                    $alumno->clases_reprobadas = 0;
                }

                if ($alumno->clases_reprobadas === 0) {
                    $aprobadosLimpios++;
                    $alumno->estado_texto = 'Aprobado Limpio';
                    $alumno->estado_color = 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20';
                } elseif ($alumno->clases_reprobadas <= 2) {
                    $enReparacion++;
                    $alumno->estado_texto = 'Reparación';
                    $alumno->estado_color = 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20';
                } else {
                    $riesgoCritico++;
                    $alumno->estado_texto = 'Repitente / Crítico';
                    $alumno->estado_color = 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20';
                }
            }

            $alumnos = $alumnos->sortByDesc('promedio_global')->values();
            $pctAprobados = $totalAlumnos > 0 ? (int) round(($aprobadosLimpios / $totalAlumnos) * 100) : 0;
            $pctReparacion = $totalAlumnos > 0 ? (int) round(($enReparacion / $totalAlumnos) * 100) : 0;
            $pctRiesgo = $totalAlumnos > 0 ? (int) round(($riesgoCritico / $totalAlumnos) * 100) : 0;

            return view('academico.docente-guia.mis-alumnos', compact(
                'aula',
                'alumnos',
                'totalAlumnos',
                'aprobadosLimpios',
                'pctAprobados',
                'enReparacion',
                'pctReparacion',
                'riesgoCritico',
                'pctRiesgo'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al cargar el listado de tus alumnos.'));
        }
    }

    public function rendimiento(Request $request)
    {
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            if (!$usuario->hasAnyRole(['docente_guia', 'Docente Guia'])) {
                return redirect()->route('dashboard')->with('error', 'Solo el Docente Guía puede acceder a la analítica de rendimiento de su aula.');
            }

            $docente = Docente::where('usuario_id', $usuario->id)->first();
            if (!$docente) {
                return redirect()->route('dashboard')->with('error', 'No se encontró su perfil de docente asociado.');
            }

            $aula = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                ->where('docente_guia_id', $docente->id)
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tiene un aula asignada en el ciclo actual.');
            }

            $corteId = $request->query('corte_evaluativo_id');
            $rendimientoAula = app(ReporteService::class)->rendimientoAula($aula, $corteId ? (int) $corteId : null);
            $cortes = CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)->orderBy('numero')->get();

            return view('academico.docente-guia.rendimiento', compact('aula', 'rendimientoAula', 'cortes', 'corteId'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error inesperado al procesar las estadísticas de rendimiento de tu aula.'));
        }
    }

    public function miHorario()
    {
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            if (!$usuario->hasAnyRole(['docente_guia', 'Docente Guia'])) {
                return redirect()->route('dashboard')->with('error', 'Solo el Docente Guía puede consultar el horario de tutoría.');
            }

            $docente = Docente::where('usuario_id', $usuario->id)->first();
            if (!$docente) {
                return redirect()->route('dashboard')->with('error', 'No se encontró su perfil de docente.');
            }

            $aula = Aula::with(['grado', 'modalidad', 'docenteGuia.usuario'])
                ->where('docente_guia_id', $docente->id)
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tiene un aula asignada como Maestro Guía en el ciclo activo.');
            }

            $asignaciones = AulaAsignaturaDocente::where('aula_id', $aula->id)->pluck('id');
            $horarios = Horario::with(['bloqueHorario', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.docente.usuario'])
                ->whereIn('aula_asignatura_docente_id', $asignaciones)
                ->get();

            $bloques = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                ->where('turno', $aula->turno)
                ->where('tipo_jornada', 'Regular')
                ->orderBy('hora_inicio')
                ->get();

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloques as $bloque) {
                $fila = ['bloque' => $bloque, 'dias' => []];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    $fila['dias'][$dia] = $bloque->es_recreo ? null : ($horariosIndex[$bloque->id][$diaBD] ?? null);
                }
                $matriz[] = $fila;
            }

            $desdeGuia = true;

            return view('academico.visor.horario_aula', compact('aula', 'matriz', 'dias', 'desdeGuia'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al cargar el horario de tu aula.'));
        }
    }

    public function detallesMiAula()
    {
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            if (!$usuario->hasAnyRole(['docente_guia', 'Docente Guia'])) {
                return redirect()->route('dashboard')->with('error', 'Solo el Docente Guía puede acceder a los detalles de su aula.');
            }

            $docente = Docente::where('usuario_id', $usuario->id)->first();
            if (!$docente) {
                return redirect()->route('dashboard')->with('error', 'No se encontró su perfil de docente asociado.');
            }

            $aula = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                ->where('docente_guia_id', $docente->id)
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tiene un aula asignada como Maestro Guía en el ciclo activo.');
            }

            $aula->load(['matriculas' => fn ($q) => $q->where('estado', 'activo')->with('alumno')]);
            $matriculas = $aula->matriculas->sortBy(fn ($m) => $m->alumno->nombre_completo ?? '');
            $totalAlumnos = $matriculas->count();

            $asignaciones = AulaAsignaturaDocente::with(['asignatura', 'docente.usuario', 'horarios.bloqueHorario'])
                ->where('aula_id', $aula->id)
                ->orderBy('id')
                ->get();

            $bloques = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                ->where('turno', $aula->turno)
                ->where('tipo_jornada', 'Regular')
                ->orderBy('hora_inicio')
                ->get();

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horarios = $asignaciones->pluck('horarios')->flatten();
            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloques as $bloque) {
                $fila = ['bloque' => $bloque, 'dias' => []];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    $fila['dias'][$dia] = $bloque->es_recreo ? null : ($horariosIndex[$bloque->id][$diaBD] ?? null);
                }
                $matriz[] = $fila;
            }

            $matriculaIds = $matriculas->pluck('id');
            $cortes = CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)->orderBy('numero')->get();

            $evaluacionesPorCorte = [];
            foreach ($cortes as $corte) {
                $notasCorte = Nota::whereIn('matricula_id', $matriculaIds)->where('corte_evaluativo_id', $corte->id)->get();
                $evaluacionesPorCorte[] = [
                    'corte' => $corte,
                    'total_alumnos' => $matriculaIds->count(),
                    'alumnos_con_nota' => $notasCorte->count(),
                    'promedio' => $notasCorte->count() > 0 ? round((float) $notasCorte->avg('nota_cuantitativa'), 1) : null,
                    'aprobados' => $notasCorte->where('nota_cuantitativa', '>=', 60)->count(),
                    'reprobados' => $notasCorte->where('nota_cuantitativa', '<', 60)->count(),
                ];
            }

            return view('academico.docente-guia.detalles-mi-aula', compact(
                'aula',
                'matriculas',
                'totalAlumnos',
                'asignaciones',
                'matriz',
                'dias',
                'diasBD',
                'bloques',
                'cortes',
                'evaluacionesPorCorte'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al cargar los detalles de tu aula.'));
        }
    }
}