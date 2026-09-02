<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Aviso;
use App\Models\Docente;
use App\Models\AulaAsignaturaDocente;
use App\Models\BloqueHorario;
use App\Models\AsistenciaPersonal;
use Carbon\Carbon;

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
            $totalDocentes = \App\Models\Usuario::role(['Docente Guia', 'Docente por Asignatura'])->count(); 
            
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

            foreach ($asignaciones as $asignacion) {
                foreach ($asignacion->horarios as $horario) {
                    if ($horario->bloqueHorario->tipo_jornada === $esquemaActivo) {
                        $matrizHorario[$horario->dia_semana][$horario->bloqueHorario->hora_inicio] = [
                            'asignacion_id' => $asignacion->id,
                            'asignatura' => $asignacion->asignatura->nombre,
                            'aula' => $asignacion->aula->grado->nombre . ' - ' . $asignacion->aula->nombre,
                            'modalidad_id' => $asignacion->aula->modalidad_id,
                            'hora_inicio' => Carbon::parse($horario->bloqueHorario->hora_inicio)->format('h:i A'),
                            'hora_fin' => Carbon::parse($horario->bloqueHorario->hora_fin)->format('h:i A')
                        ];
                    }
                }
            }
        }

        return view('dashboard', compact(
            'avisos', 
            'asistenciaHoy', 
            'docente', 
            'asignaciones',
            'bloques', 
            'diasSemana', 
            'matrizHorario', 
            'esquemaActivo',
            'totalAlumnos',      // <--- Añadido
            'totalMatriculados', // <--- Añadido
            'totalPersonal'      // <--- Añadido
        ));
    }

    // --- MÉTODOS DE AVISOS (DIRECTIVA) ---
    public function storeAviso(Request $request)
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector', 'Coordinador'])) { abort(403); }

        $request->validate([
            'titulo' => 'required|string|max:120', 
            'mensaje' => 'required|string|max:1000'
        ]);

        try {
            DB::table('aviso')->insert([
                'titulo' => $request->titulo, 
                'mensaje' => $request->mensaje, 
                'autor_id' => Auth::id(),
                'activo' => true, 
                'created_at' => now(), 
                'updated_at' => now(),
            ]);
            return redirect()->route('dashboard')->with('success', 'Comunicado publicado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Error al publicar: ' . $e->getMessage());
        }
    }

    public function updateAviso(Request $request, $id)
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector', 'Coordinador'])) { abort(403); }

        $request->validate(['titulo' => 'required|string|max:120', 'mensaje' => 'required|string|max:1000']);
        Aviso::where('id', $id)->update(['titulo' => $request->titulo, 'mensaje' => $request->mensaje, 'updated_at' => now()]);
        return redirect()->route('dashboard')->with('success', 'Comunicado actualizado.');
    }

    public function destroyAviso($id)
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector', 'Coordinador'])) { abort(403); }

        Aviso::where('id', $id)->delete();
        return redirect()->route('dashboard')->with('success', 'Comunicado eliminado.');
    }
}