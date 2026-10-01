<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Aula;
use App\Models\Alumno;
use Illuminate\Http\Request;

class DocenteGuiaController extends Controller
{
    public function misAlumnos()
    {
        try {
            $usuario = auth()->user();
            $docente = Docente::where('usuario_id', $usuario->id)->firstOrFail();
            
            // 1. Validar que tenga un aula asignada
            $aula = Aula::with('grado')->where('docente_guia_id', $docente->id)->first();
            
            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como Maestro Guía.');
            }

            // 2. Traer alumnos con su matrícula activa y sus notas consolidadas
            $alumnos = Alumno::whereHas('matriculas', function($q) use ($aula) {
                $q->where('aula_id', $aula->id)->where('estado', 'activo');
            })->with(['matriculas' => function($q) use ($aula) {
                $q->where('aula_id', $aula->id)->where('estado', 'activo')->with('notas');
            }])->get();

            // 3. Variables para los KPIs de la cabecera
            $totalAlumnos = $alumnos->count();
            $aprobadosLimpios = 0;
            $enReparacion = 0;
            $riesgoCritico = 0;

            // 4. Lógica Matemática por Alumno
            foreach ($alumnos as $alumno) {
                $matricula = $alumno->matriculas->first();
                $notas = $matricula ? $matricula->notas : collect();

                if ($notas->count() > 0) {
                    $alumno->promedio_global = round($notas->avg('nota_cuantitativa'), 2);
                    $alumno->clases_reprobadas = $notas->where('nota_cuantitativa', '<', 60)->count(); 
                } else {
                    $alumno->promedio_global = 0;
                    $alumno->clases_reprobadas = 0;
                }

                // Categorización Automática
                if ($alumno->clases_reprobadas == 0) {
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

            // 5. Ordenar el ranking (Los mejores promedios primero)
            $alumnos = $alumnos->sortByDesc('promedio_global')->values();

            // 6. Porcentajes
            $pctAprobados = $totalAlumnos > 0 ? round(($aprobadosLimpios / $totalAlumnos) * 100) : 0;
            $pctReparacion = $totalAlumnos > 0 ? round(($enReparacion / $totalAlumnos) * 100) : 0;
            $pctRiesgo = $totalAlumnos > 0 ? round(($riesgoCritico / $totalAlumnos) * 100) : 0;

            return view('academico.docente-guia.mis-alumnos', compact(
                'aula', 'alumnos', 'totalAlumnos', 
                'aprobadosLimpios', 'pctAprobados', 
                'enReparacion', 'pctReparacion', 
                'riesgoCritico', 'pctRiesgo'
            ));

        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Ocurrió un problema al cargar el listado de tus alumnos. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Rendimiento por corte del aula/grado que guía el docente (solo Docente Guía).
     */
    public function rendimiento(Request $request)
    {
        try {
            $usuario = auth()->user();

            if (!$usuario->hasRole('Docente Guia')) {
                return redirect()->route('dashboard')->with('error', 'Solo el Docente Guía puede acceder a la analítica de rendimiento de su aula.');
            }

            $docente = Docente::where('usuario_id', $usuario->id)->first();
            if (!$docente) {
                return redirect()->route('dashboard')->with('error', 'No se encontró su perfil de docente asociado.');
            }

            $aula = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                ->where('docente_guia_id', $docente->id)
                ->whereHas('anioEscolar', fn ($q) => $q->where('activo', true))
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tiene un aula asignada en el ciclo actual.');
            }

            $corteId = $request->query('corte_evaluativo_id');
            $rendimientoAula = app(\App\Services\ReporteService::class)->rendimientoAula($aula, $corteId ? (int) $corteId : null);

            $cortes = \App\Models\CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)->orderBy('numero')->get();

            return view('academico.docente-guia.rendimiento', compact('aula', 'rendimientoAula', 'cortes', 'corteId'));

        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error inesperado al procesar las estadísticas de rendimiento de tu aula.');
        }
    }

    /**
     * Horario exclusivo del aula asignada al Docente Guía (reutiliza la vista limpia sin exponer otras aulas).
     */
    public function miHorario()
    {
        try {
            $usuario = auth()->user();

            if (!$usuario->hasRole('Docente Guia')) {
                return redirect()->route('dashboard')->with('error', 'Solo el Docente Guía puede consultar el horario de tutoría.');
            }

            $docente = Docente::where('usuario_id', $usuario->id)->first();
            if (!$docente) {
                return redirect()->route('dashboard')->with('error', 'No se encontró su perfil de docente.');
            }

            // Busca estrictamente el aula activa asignada a este Docente Guía
            $aula = Aula::with(['grado', 'modalidad', 'docenteGuia.usuario'])
                ->where('docente_guia_id', $docente->id)
                ->whereHas('anioEscolar', fn ($q) => $q->where('activo', true))
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tiene un aula asignada como Maestro Guía en el ciclo activo.');
            }

            $asignaciones = \App\Models\AulaAsignaturaDocente::where('aula_id', $aula->id)->pluck('id');

            $horarios = \App\Models\Horario::with(['bloque', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.docente.usuario'])
                ->whereIn('aula_asignatura_docente_id', $asignaciones)
                ->get();

            $bloques = \App\Models\BloqueHorario::where('modalidad_id', $aula->modalidad_id)
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

            // Activa el retorno directo a la pestaña Docente Guía del Dashboard
            $desdeGuia = true;

            return view('academico.visor.horario_aula', compact('aula', 'matriz', 'dias', 'desdeGuia'));

        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Ocurrió un problema al cargar el horario de tu aula.');
        }
    }
}