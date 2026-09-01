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
        $dbMetricas = []; // Nueva variable que contendrá TODAS las gráficas

        // 3. CARGA DE DATOS PARA DIRECTIVA Y GESTIÓN
        if ($user->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
            $totalAlumnos = \App\Models\Alumno::count();
            $totalDocentes = \App\Models\Usuario::role(['Docente Guía', 'Docente por Asignatura'])->count(); 
            
            // --- PRIMERA CONSULTA 100% REAL (Mejorada con Eloquent) ---
            try {
                // Usamos LIKE para que detecte "Preescolar", "PREESCOLAR", "Educación Preescolar", etc.
                $preescolar = \App\Models\Matricula::whereHas('aula.modalidad', function($q) {
                    $q->where('nombre', 'LIKE', '%reescolar%'); 
                })->count();

                $primaria = \App\Models\Matricula::whereHas('aula.modalidad', function($q) {
                    $q->where('nombre', 'LIKE', '%rimaria%');
                })->count();

                $secundaria = \App\Models\Matricula::whereHas('aula.modalidad', function($q) {
                    $q->where('nombre', 'LIKE', '%ecundaria%');
                })->count();
            } catch (\Exception $e) {
                // Si hay algún problema con las relaciones o tablas vacías, previene el error 500
                $preescolar = 0; $primaria = 0; $secundaria = 0;
            }

            // --- ARMAMOS EL PAQUETE DE GRÁFICAS PARA ENVIAR A JAVASCRIPT ---
            $dbMetricas = [
                'matriculados'          => ['titulo' => 'Alumnos Matriculados Activos (Dato Real)', 'datos' => [$preescolar, $primaria, $secundaria]],
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

        // 4. CARGA DE DATOS PARA DOCENTE POR ASIGNATURA (Horarios)
        if ($user->hasRole('Docente por Asignatura')) {
            $docenteId = $user->docente->id ?? null;
            if ($docenteId) {
                $horarios = \App\Models\Horario::with([
                        'bloqueHorario', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.aula.grado', 'aulaAsignaturaDocente.aula.modalidad'
                    ])
                    ->whereHas('aulaAsignaturaDocente', function($q) use ($docenteId) {
                        $q->where('docente_id', $docenteId);
                    })
                    ->get()
                    ->sortBy(fn($horario) => $horario->bloqueHorario->hora_inicio)
                    ->groupBy('dia_semana');
            }
        }

        // 5. ENRUTAMIENTO ÚNICO AL DASHBOARD COMPONENTIZADO
        return view('dashboard', compact(
            'avisos', 'totalAlumnos', 'totalDocentes', 'horarios', 'diasSemana', 'dbMetricas'
        ));
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