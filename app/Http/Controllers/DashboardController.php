<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
   public function index()
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // 1. DATOS GLOBALES (Avisos para todos)
        $avisos = DB::table('aviso')
            ->join('usuario', 'aviso.autor_id', '=', 'usuario.id')
            ->select('aviso.*', 'usuario.nombre_completo as autor_nombre')
            ->where('aviso.activo', true)
            ->orderBy('aviso.created_at', 'desc')
            ->take(5)
            ->get();

        // 2. INICIALIZAR VARIABLES POR DEFECTO
        $totalAlumnos = 0;
        $totalDocentes = 0;
        $horarios = collect();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $dbMetricas = []; 
        $aulaGuia = null; // Inicializamos la variable para el Maestro Guía

        // 3. CARGA DE DATOS PARA DIRECTIVA Y GESTIÓN
        if ($user->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
            $totalAlumnos = \App\Models\Alumno::count();
            $totalDocentes = \App\Models\Usuario::role(['Docente Guía', 'Docente por Asignatura'])->count();

            // --- PRIMERA CONSULTA 100% REAL ---
            $matriculasActivas = DB::table('matricula')
                ->join('aula', 'matricula.aula_id', '=', 'aula.id')
                ->join('modalidad', 'aula.modalidad_id', '=', 'modalidad.id')
                ->select('modalidad.nombre', DB::raw('count(matricula.id) as total'))
                ->groupBy('modalidad.nombre')
                ->pluck('total', 'nombre');

            $preescolar = $matriculasActivas['Preescolar'] ?? 0;
            $primaria   = $matriculasActivas['Primaria'] ?? 0;
            $secundaria = $matriculasActivas['Secundaria'] ?? 0;

            // --- ARMAMOS EL PAQUETE DE GRÁFICAS PARA ENVIAR A JAVASCRIPT ---
            $dbMetricas = [
                'matriculados'          => ['titulo' => 'Alumnos Matriculados (Dato Real)', 'datos' => [$preescolar, $primaria, $secundaria]],
                'asistencia_alumnos'    => ['titulo' => 'Asistencia de Alumnos (%)', 'datos' => [0, 0, 0]],
                'asistencia_docentes'   => ['titulo' => 'Asistencia de Docentes (%)', 'datos' => [0, 0, 0]],
                'rendimiento_modalidad' => ['titulo' => 'Rendimiento Académico General (%)', 'datos' => [0, 0, 0]],
                'apoyo_padres'          => ['titulo' => 'Participación de Padres (%)', 'datos' => [0, 0, 0]],
                'aprobados'             => ['titulo' => 'Alumnos Aprobados Limpios (%)', 'datos' => [0, 0, 0]],
                'reprobados_leves'      => ['titulo' => 'Reprobados (1 a 2 Clases) (%)', 'datos' => [0, 0, 0]],
                'reprobados_graves'     => ['titulo' => 'Reprobados Críticos (3+ Clases) (%)', 'datos' => [0, 0, 0]],
                'promedio_notas'        => ['titulo' => 'Promedio de Calificaciones (Escala 0-100)', 'datos' => [0, 0, 0]],
                'avances_silabo'        => ['titulo' => 'Avance Curricular del Maestro (%)', 'datos' => [0, 0, 0]],
                'retencion'             => ['titulo' => 'Retención Estudiantil (%)', 'datos' => [0, 0, 0]],
                'puntualidad'           => ['titulo' => 'Puntualidad en Horario de Entrada (%)', 'datos' => [0, 0, 0]]
            ];
        }

        // 4. CARGA DE DATOS PARA DOCENTES (Guía y Asignatura)
        $docente = \App\Models\Docente::where('usuario_id', $user->id)->first();
        
        if ($docente) {
            // Lógica para Docente Guía: Buscamos el aula que tiene a su cargo
            if ($user->hasRole('Docente Guia')) {
                $aulaGuia = \App\Models\Aula::with('grado')->where('docente_guia_id', $docente->id)->first();
            }

            // Lógica para Docente por Asignatura: Buscamos sus horarios
            if ($user->hasRole('Docente por Asignatura')) {
                $horarios = \App\Models\Horario::with([
                        'bloqueHorario',
                        'aulaAsignaturaDocente.asignatura',
                        'aulaAsignaturaDocente.aula.grado',
                        'aulaAsignaturaDocente.aula.modalidad'
                    ])
                    ->whereHas('aulaAsignaturaDocente', function($q) use ($docente) {
                        $q->where('docente_id', $docente->id);
                    })
                    ->get()
                    ->sortBy(fn($horario) => $horario->bloqueHorario->hora_inicio)
                    ->groupBy('dia_semana');
            }
        }

        // 5. ENRUTAMIENTO ÚNICO AL DASHBOARD COMPONENTIZADO
        // Es vital que 'aulaGuia' vaya aquí en el compact
        return view('dashboard', compact(
            'avisos', 'totalAlumnos', 'totalDocentes', 'horarios', 'diasSemana', 'dbMetricas', 'aulaGuia'
        ));
    }

    /**
     * Calcula las métricas para las gráficas del panel directivo, segmentadas
     * por modalidad (Preescolar, Primaria, Secundaria).
     */
    protected function calcularMetricas(): array
    {
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
            $aulaIds = \App\Models\Aula::where('modalidad_id', $modalidad->id)->pluck('id');

            // Matriculados activos
            $matriculaQuery = \App\Models\Matricula::whereIn('aula_id', $aulaIds);
            $activos = (clone $matriculaQuery)->where('estado', 'activo')->count();
            $retirados = (clone $matriculaQuery)->where('estado', 'retirado')->count();
            $matriculados[] = $activos;

            $matriculaIds = (clone $matriculaQuery)->where('estado', 'activo')->pluck('id');

            // Asistencia de alumnos (% presentes)
            $asistAula = \App\Models\AsistenciaAula::whereIn('matricula_id', $matriculaIds)->get();
            $totalRegAula = $asistAula->count();
            $presentesAula = $asistAula->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
            $asistenciaAlumnos[] = $totalRegAula > 0 ? round(($presentesAula / $totalRegAula) * 100, 1) : 0;

            // Puntualidad (% Presente sin retardo) sobre asistencia personal del día
            // Se calcula globalmente al final; aquí dejamos acumuladores por modalidad no aplican.

            // Rendimiento académico (% aprobados con promedio >= 60)
            $notasModalidad = \App\Models\Nota::whereIn('matricula_id', $matriculaIds)->get();
            $aprob = $notasModalidad->where('nota_cuantitativa', '>=', 60)->count();
            $totalNotas = $notasModalidad->count();
            $rendimiento[] = $totalNotas > 0 ? round(($aprob / $totalNotas) * 100, 1) : 0;

            // Promedio de calificaciones (escala 0-100)
            $promedioNotas[] = $totalNotas > 0 ? round((float) $notasModalidad->avg('nota_cuantitativa'), 2) : 0;

            // Apoyo de padres (% de padres que apoyan)
            $apoyo = \App\Models\ApoyoPadres::whereIn('aula_id', $aulaIds)->get();
            $totApoyo = $apoyo->sum('total_padres');
            $cantApoyo = $apoyo->sum('cantidad_apoyan');
            $apoyoPadres[] = $totApoyo > 0 ? round(($cantApoyo / $totApoyo) * 100, 1) : 0;

            // Aprobados limpios / reprobados por alumno (conteo de asignaturas reprobadas)
            $alumnosNotas = \App\Models\Nota::whereIn('matricula_id', $matriculaIds)
                ->get()
                ->groupBy('matricula_id');
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

            // Avance curricular (promedio de porcentaje de avance)
            $asignacionIds = \App\Models\AulaAsignaturaDocente::whereIn('aula_id', $aulaIds)->pluck('id');
            $avancesMod = \App\Models\AvanceContenido::whereIn('aula_asignatura_docente_id', $asignacionIds)->get();
            $avances[] = $avancesMod->count() > 0 ? round((float) $avancesMod->avg('porcentaje_avance'), 1) : 0;

            // Retención = activos / (activos + retirados)
            $retencion[] = ($activos + $retirados) > 0 ? round(($activos / ($activos + $retirados)) * 100, 1) : 0;
        }

        // Puntualidad y asistencia de docentes: globales (AsistenciaPersonal)
        $docentesIds = \App\Models\Usuario::role(['Docente Guia', 'Docente por Asignatura'])->pluck('id');
        $asisPersonal = \App\Models\AsistenciaPersonal::whereIn('usuario_id', $docentesIds)->get();
        $totPersonal = $asisPersonal->count();
        $presentesPersonal = $asisPersonal->where('estado', 'Presente')->count();
        $retardos = $asisPersonal->where('estado', 'Retardo')->count();
        $asistenciaDocentes[] = $totPersonal > 0 ? round(($presentesPersonal / $totPersonal) * 100, 1) : 0;
        $puntualidad[] = $totPersonal > 0 ? round((($presentesPersonal) / $totPersonal) * 100, 1) : 0;

        // Para puntualidad distribuimos el mismo valor (no hay segmentación por modalidad en AsistenciaPersonal)
        $puntualidad = array_fill(0, count($modalidades), count($puntualidad) ? $puntualidad[0] : 0);

        // asistencia_docentes también puede repetirse por modalidad (sin segmentación)
        $asistenciaDocentesArr = array_fill(0, count($modalidades), $asistenciaDocentes[0] ?? 0);

        return [
            'matriculados'          => ['titulo' => 'Alumnos Matriculados Activos', 'datos' => $matriculados],
            'asistencia_alumnos'    => ['titulo' => 'Asistencia de Alumnos (%)', 'datos' => $asistenciaAlumnos],
            'asistencia_docentes'   => ['titulo' => 'Asistencia de Docentes (%)', 'datos' => $asistenciaDocentesArr],
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
    }

    public function storeAviso(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) { abort(403); }

        $request->validate(['titulo' => 'required|string|max:120', 'mensaje' => 'required|string|max:1000']);
        DB::table('aviso')->insert([
            'titulo' => $request->titulo, 'mensaje' => $request->mensaje, 'autor_id' => Auth::id(),
            'activo' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Aviso publicado correctamente.');
    }

    public function updateAviso(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) { abort(403); }

        $request->validate(['titulo' => 'required|string|max:120', 'mensaje' => 'required|string|max:1000']);
        DB::table('aviso')->where('id', $id)->update(['titulo' => $request->titulo, 'mensaje' => $request->mensaje, 'updated_at' => now()]);

        return redirect()->route('dashboard')->with('success', 'Aviso actualizado correctamente.');
    }

    public function destroyAviso($id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) { abort(403); }

        DB::table('aviso')->where('id', $id)->delete();
        return redirect()->route('dashboard')->with('success', 'Aviso eliminado del sistema.');
    }
}
