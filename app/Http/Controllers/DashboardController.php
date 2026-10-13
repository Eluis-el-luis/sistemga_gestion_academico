<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Registra el error real en storage/logs/laravel.log y devuelve el detalle
     * técnico solo si APP_DEBUG=true (entorno local).
     */
    protected function formatearError(\Throwable $e, string $mensajeAmigable): string
    {
        \Illuminate\Support\Facades\Log::error($mensajeAmigable . ' | Error: ' . $e->getMessage(), [
            'archivo' => $e->getFile(),
            'linea'   => $e->getLine(),
            'usuario' => auth()->id(),
        ]);

        if (config('app.debug')) {
            $archivoCorto = basename($e->getFile());
            return "{$mensajeAmigable} — [Detalle Técnico: {$e->getMessage()} en {$archivoCorto} (Línea {$e->getLine()})]";
        }

        return $mensajeAmigable;
    }

    public function index()
    {
        try {
            /** @var \App\Models\Usuario $user */
            $user = Auth::user();
            $docente = \App\Models\Docente::where('usuario_id', $user->id)->first();

            // 1. CARGA DE MODALIDADES (Necesario para el select del modal Nuevo Comunicado)
            $modalidades = \App\Models\Modalidad::orderBy('id')->get();

            // 2. FILTRADO INTELIGENTE DE AVISOS
            $queryAvisos = DB::table('aviso')
                ->join('usuario', 'aviso.autor_id', '=', 'usuario.id')
                ->select('aviso.*', 'usuario.nombre_completo as autor_nombre')
                ->where('aviso.activo', true);

            // Si NO es Directiva, aplicamos el filtro de lectura segmentado
            if (!$user->hasAnyRole(['Director', 'Subdirector'])) {
                $modalidadesPermitidas = [];

                if ($user->hasRole('Coordinador') && $docente) {
                    $modalidadesPermitidas[] = $docente->modalidad_coordina_id;
                }

                if ($user->hasAnyRole(['Docente Guia', 'Docente por Asignatura']) && $docente) {
                    // Rastrear todas las modalidades donde el maestro tiene carga horaria activa
                    $modalidadesClase = \App\Models\AulaAsignaturaDocente::where('docente_id', $docente->id)
                        ->where('activo', true)
                        ->join('aula', 'aula_asignatura_docente.aula_id', '=', 'aula.id')
                        ->pluck('aula.modalidad_id')
                        ->toArray();
                    
                    $modalidadesPermitidas = array_merge($modalidadesPermitidas, $modalidadesClase);
                }

                $modalidadesPermitidas = array_filter(array_unique($modalidadesPermitidas));

                $queryAvisos->where(function($q) use ($modalidadesPermitidas) {
                    $q->whereNull('aviso.modalidad_id'); // Siempre ver comunicados globales
                    if (!empty($modalidadesPermitidas)) {
                        $q->orWhereIn('aviso.modalidad_id', $modalidadesPermitidas); // Ver comunicados de sus áreas
                    }
                });
            }

            $avisos = $queryAvisos->orderBy('aviso.created_at', 'desc')->take(5)->get();

            // 3. INICIALIZAR VARIABLES
            $totalMatriculados = 0;
            $totalPersonal = 0;
            $horarios = collect();
            $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];
            $diaDisplay = array_combine($diasBD, $diasSemana);
            $dbMetricas = []; 
            $aulaGuia = null; 
            $esDocenteGuia = false;
            $bloques = collect();
            $matrizHorario = [];
            $esquemaActivo = 'Regular';

            // 4. CARGA DE DATOS PARA DIRECTIVA Y GESTIÓN
            if ($user->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
                $anioActivo = \App\Models\AnioEscolar::where('activo', true)->first();
                // Ahora el total de matriculados es estrictamente los de este año
                $totalMatriculados = \App\Models\Matricula::where('estado', 'activo')
                    ->when($anioActivo, fn($q) => $q->where('anio_escolar_id', $anioActivo->id))
                    ->count();
                $totalPersonal = \App\Models\Usuario::role(['Docente Guia', 'Docente por Asignatura'])->count();
                $dbMetricas = $this->calcularMetricas();
            }

            // 5. CARGA DE DATOS PARA DOCENTES (Guía y Asignatura)
            $anioActivo = \App\Models\AnioEscolar::where('activo', true)->first();
            
            if ($docente && $anioActivo) {
                $aulaGuia = \App\Models\Aula::with('grado')
                    ->where('docente_guia_id', $docente->id)
                    ->where('anio_escolar_id', $anioActivo->id)
                    ->first();

                $esDocenteGuia = $aulaGuia ? true : false;

                $horariosRaw = \App\Models\Horario::with([
                        'bloqueHorario',
                        'aulaAsignaturaDocente.asignatura',
                        'aulaAsignaturaDocente.aula.grado',
                        'aulaAsignaturaDocente.aula.modalidad'
                    ])
                    ->whereHas('aulaAsignaturaDocente', function($q) use ($docente, $anioActivo) {
                        $q->where('docente_id', $docente->id)
                          ->where('anio_escolar_id', $anioActivo->id)
                          ->where('activo', true); 
                    })
                    ->get();

                $bloques = $horariosRaw->pluck('bloqueHorario')->unique('hora_inicio')->sortBy('hora_inicio')->values();
                    
                if($bloques->count() > 0) {
                    $esquemaActivo = $bloques->first()->tipo_jornada ?? 'Regular';
                }

                foreach ($horariosRaw as $horario) {
                    $dia = $diaDisplay[$horario->dia_semana] ?? $horario->dia_semana;
                    $hora = $horario->bloqueHorario->hora_inicio;

                    $matrizHorario[$dia][$hora] = [
                        'asignacion_id' => $horario->aula_asignatura_docente_id,
                        'asignatura'    => $horario->aulaAsignaturaDocente->asignatura->nombre,
                        'aula'          => $horario->aulaAsignaturaDocente->aula->grado->nombre . ' - ' . $horario->aulaAsignaturaDocente->aula->nombre,
                        'hora_inicio'   => $horario->bloqueHorario->hora_inicio,
                        'hora_fin'      => $horario->bloqueHorario->hora_fin,
                        'modalidad_id'  => $horario->aulaAsignaturaDocente->aula->modalidad_id,
                    ];
                }
            }

            // 5. KPIs POR ROL (datos reales)
            $docentesSinMarcar = collect();
            $solicitudesPendientes = collect();
            $asistenciaSemanal = null;
            $rendimientoAula = null;

            if ($user->hasRole('Coordinador')) {
                $hoy = now()->timezone('America/Managua')->toDateString();
                $docentesIds = \App\Models\Usuario::role(['Docente Guia', 'Docente por Asignatura'])->pluck('id');
                $docentesQueMarcaron = \App\Models\AsistenciaPersonal::where('fecha', $hoy)
                    ->whereIn('usuario_id', $docentesIds)
                    ->pluck('usuario_id');

                $docentesSinMarcar = \App\Models\Usuario::role(['Docente Guia', 'Docente por Asignatura'])
                    ->whereNotIn('id', $docentesQueMarcaron)
                    ->whereHas('docente')
                    ->get();

                $solicitudesPendientes = \App\Models\SolicitudEdicionNota::with(['docente.usuario', 'nota.aulaAsignaturaDocente.asignatura'])
                    ->where('estado', 'Pendiente')
                    ->orderByDesc('created_at')
                    ->get();
            }

            if ($aulaGuia) {
                $matriculaIds = \App\Models\Matricula::where('aula_id', $aulaGuia->id)->where('estado', 'activo')->pluck('id');

                $inicioSemana = now()->timezone('America/Managua')->startOfWeek();
                $finSemana = now()->timezone('America/Managua')->endOfWeek();

                $asistencias = \App\Models\AsistenciaAula::whereIn('matricula_id', $matriculaIds)
                    ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
                    ->get();

                $totalRegistros = $asistencias->count();
                $presentes = $asistencias->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();

                $asistenciaSemanal = [
                    'porcentaje' => $totalRegistros > 0 ? round(($presentes / $totalRegistros) * 100, 1) : 0,
                    'total_matriculas' => $matriculaIds->count(),
                ];

                $rendimientoAula = app(\App\Services\ReporteService::class)->rendimientoAula($aulaGuia);
            }

            return view('dashboard', compact(
                'avisos', 'totalMatriculados', 'totalPersonal', 'diasSemana', 'dbMetricas', 'aulaGuia', 'esDocenteGuia', 'bloques', 'matrizHorario', 'esquemaActivo',
                'docentesSinMarcar', 'solicitudesPendientes', 'asistenciaSemanal', 'rendimientoAula'
            ));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return view('dashboard', [
                'avisos' => collect(), 'totalMatriculados' => 0, 'totalPersonal' => 0,
                'diasSemana' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
                'dbMetricas' => [], 'aulaGuia' => null, 'esDocenteGuia' => false,
                'bloques' => collect(), 'matrizHorario' => [], 'esquemaActivo' => 'Regular',
                'docentesSinMarcar' => collect(), 'solicitudesPendientes' => collect(),
                'asistenciaSemanal' => null, 'rendimientoAula' => null
            ])->with('error', $this->formatearError($e, 'Ocurrió un problema técnico al cargar algunos datos del panel.'));
        }
    }

    /**
     * Calcula las métricas para las gráficas del panel directivo, segmentadas
     * por modalidad y EXCLUSIVAMENTE limitadas al Año Escolar Activo.
     */
    protected function calcularMetricas(): array
    {
        try {
            $anioActivo = \App\Models\AnioEscolar::where('activo', true)->first();
            
            // Si el colegio no ha aperturado un año escolar, devolvemos gráficas vacías seguras
            if (!$anioActivo) {
                return [];
            }

            $modalidades = \App\Models\Modalidad::orderBy('id')->get();

            $matriculados = [];
            $asistenciaAlumnos = [];
            $asistenciaDocentes = [];
            $rendimiento = [];
            $apoyoPadres = [];
            $aprobados = [];
            $reprobadosLeves = [];
            $reprobadosGraves = [];
            $promedioNotas = [];
            $avances = [];
            $retencion = [];
            $puntualidad = [];

            foreach ($modalidades as $modalidad) {
                // 1. AULAS ESTRICTAMENTE DEL AÑO ESCOLAR ACTUAL
                $aulaIds = \App\Models\Aula::where('modalidad_id', $modalidad->id)
                    ->where('anio_escolar_id', $anioActivo->id)
                    ->pluck('id');

                // 2. MATRÍCULAS (Activas y Retiradas) DE ESTE AÑO
                $matriculaQuery = \App\Models\Matricula::whereIn('aula_id', $aulaIds);
                $activos = (clone $matriculaQuery)->where('estado', 'activo')->count();
                $retirados = (clone $matriculaQuery)->where('estado', 'retirado')->count();
                
                $matriculados[] = $activos;
                $retencion[] = ($activos + $retirados) > 0 ? round(($activos / ($activos + $retirados)) * 100, 1) : 0;

                // Capturamos los IDs de matrículas activas para los siguientes cálculos
                $matriculaIds = (clone $matriculaQuery)->where('estado', 'activo')->pluck('id');

                // 3. ASISTENCIA ALUMNOS (De este año)
                $asistAula = \App\Models\AsistenciaAula::whereIn('matricula_id', $matriculaIds)->get();
                $totalRegAula = $asistAula->count();
                $presentesAula = $asistAula->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
                $asistenciaAlumnos[] = $totalRegAula > 0 ? round(($presentesAula / $totalRegAula) * 100, 1) : 0;

                // 4. RENDIMIENTO ACADÉMICO Y NOTAS (De este año)
                $notasModalidad = \App\Models\Nota::whereIn('matricula_id', $matriculaIds)->get();
                $totalNotas = $notasModalidad->count();
                $aprob = $notasModalidad->where('nota_cuantitativa', '>=', 60)->count();
                
                $rendimiento[] = $totalNotas > 0 ? round(($aprob / $totalNotas) * 100, 1) : 0;
                $promedioNotas[] = $totalNotas > 0 ? round((float) $notasModalidad->avg('nota_cuantitativa'), 2) : 0;

                // Clasificación de Riesgo Estudiantil (Aprobados limpios vs Aplazados)
                $alumnosNotas = $notasModalidad->groupBy('matricula_id');
                $limpios = 0; $leves = 0; $graves = 0; $totalAlumnosEval = $alumnosNotas->count();
                
                foreach ($alumnosNotas as $notasAlumno) {
                    $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();
                    if ($reprobadas === 0) $limpios++;
                    elseif ($reprobadas <= 2) $leves++;
                    else $graves++;
                }
                
                $aprobados[] = $totalAlumnosEval > 0 ? round(($limpios / $totalAlumnosEval) * 100, 1) : 0;
                $reprobadosLeves[] = $totalAlumnosEval > 0 ? round(($leves / $totalAlumnosEval) * 100, 1) : 0;
                $reprobadosGraves[] = $totalAlumnosEval > 0 ? round(($graves / $totalAlumnosEval) * 100, 1) : 0;

                // 5. PARTICIPACIÓN FAMILIAR (De este año)
                $apoyo = \App\Models\ApoyoPadres::whereIn('aula_id', $aulaIds)->get();
                $totApoyo = $apoyo->sum('total_padres');
                $cantApoyo = $apoyo->sum('cantidad_apoyan');
                $apoyoPadres[] = $totApoyo > 0 ? round(($cantApoyo / $totApoyo) * 100, 1) : 0;

                // 6. AVANCE CURRICULAR DE DOCENTES
                $asignacionIds = \App\Models\AulaAsignaturaDocente::whereIn('aula_id', $aulaIds)->pluck('id');
                $avancesMod = \App\Models\AvanceContenido::whereIn('aula_asignatura_docente_id', $asignacionIds)->get();
                $avances[] = $avancesMod->count() > 0 ? round((float) $avancesMod->avg('porcentaje_avance'), 1) : 0;

                // 7. PUNTUALIDAD Y ASISTENCIA DE DOCENTES (Filtrado por año y modalidad base)
                $docentesIds = \App\Models\Docente::where('modalidad_id', $modalidad->id)->pluck('usuario_id');
                
                $fechaInicio = $anioActivo->fecha_inicio ?? now()->startOfYear()->toDateString();
                $fechaFin = $anioActivo->fecha_fin ?? now()->endOfYear()->toDateString();
                
                $asisPersonal = \App\Models\AsistenciaPersonal::whereIn('usuario_id', $docentesIds)
                    ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->get();
                    
                $totPersonal = $asisPersonal->count();
                // Asistencia General = Presentes + Retardos (Llegó tarde, pero asistió a dar clase)
                $asistenciasDoc = $asisPersonal->whereIn('estado', ['Presente', 'Retardo'])->count();
                // Puntualidad = Solo Presentes a tiempo
                $puntuales = $asisPersonal->where('estado', 'Presente')->count(); 

                $asistenciaDocentes[] = $totPersonal > 0 ? round(($asistenciasDoc / $totPersonal) * 100, 1) : 0;
                $puntualidad[] = $totPersonal > 0 ? round(($puntuales / $totPersonal) * 100, 1) : 0;
            }

            return [
                'matriculados'          => ['titulo' => 'Alumnos Matriculados Activos', 'datos' => $matriculados],
                'asistencia_alumnos'    => ['titulo' => 'Asistencia de Alumnos (%)', 'datos' => $asistenciaAlumnos],
                'asistencia_docentes'   => ['titulo' => 'Asistencia de Docentes (%)', 'datos' => $asistenciaDocentes],
                'rendimiento_modalidad' => ['titulo' => 'Rendimiento Académico General (%)', 'datos' => $rendimiento],
                'apoyo_padres'          => ['titulo' => 'Participación de Padres (%)', 'datos' => $apoyoPadres],
                'aprobados'             => ['titulo' => 'Alumnos Aprobados Limpios (%)', 'datos' => $aprobados],
                'reprobados_leves'      => ['titulo' => 'Reprobados (1 a 2 Clases) (%)', 'datos' => $reprobadosLeves],
                'reprobados_graves'     => ['titulo' => 'Reprobados Críticos (3+ Clases) (%)', 'datos' => $reprobadosGraves],
                'promedio_notas'        => ['titulo' => 'Promedio de Calificaciones (Escala 0-100)', 'datos' => $promedioNotas],
                'avances_silabo'        => ['titulo' => 'Avance Curricular del Maestro (%)', 'datos' => $avances],
                'retencion'             => ['titulo' => 'Retención Estudiantil (%)', 'datos' => $retencion],
                'puntualidad'           => ['titulo' => 'Puntualidad en Horario de Entrada (%)', 'datos' => $puntualidad],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function storeAviso(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector', 'Coordinador'])) {
            return back()->with('error', 'No tienes permiso para publicar avisos.');
        }

        $request->validate([
            'titulo' => 'required|string|max:120',
            'mensaje' => 'required|string|max:1000',
            'modalidad_id' => 'nullable|exists:modalidad,id'
        ]);

        try {
            $modalidadId = $request->modalidad_id;

            if ($user->hasRole('Coordinador') && !$user->hasAnyRole(['Director', 'Subdirector'])) {
                $docente = \App\Models\Docente::where('usuario_id', $user->id)->first();
                $modalidadId = $docente->modalidad_coordina_id; 
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
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un error al intentar publicar el aviso.'));
        }
    }

    public function updateAviso(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) {
            return back()->with('error', 'No tienes permiso para editar avisos.');
        }

        $request->validate(['titulo' => 'required|string|max:120', 'mensaje' => 'required|string|max:1000']);
        
        try {
            DB::table('aviso')->where('id', $id)->update(['titulo' => $request->titulo, 'mensaje' => $request->mensaje, 'updated_at' => now()]);
            return redirect()->route('dashboard')->with('success', 'Aviso actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un problema al actualizar el aviso.'));
        }
    }

    public function destroyAviso($id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) {
            return back()->with('error', 'No tienes permiso para eliminar avisos.');
        }

        try {
            DB::table('aviso')->where('id', $id)->delete();
            return redirect()->route('dashboard')->with('success', 'Aviso eliminado del sistema.');
        } catch (\Exception $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el aviso.'));
        }
    }
}