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
        $usuario = Auth::user();
        
        // 1. Cargar Avisos Globales
        $avisos = Aviso::with('autor')->where('activo', true)->latest()->get();
        
        // 2. Cargar Asistencia Personal del Día
        $asistenciaHoy = AsistenciaPersonal::where('usuario_id', $usuario->id)
                            ->whereDate('fecha', Carbon::today())
                            ->first();

        // --- NUEVO: CONTADORES GLOBALES PARA LAS TARJETAS DEL DASHBOARD ---
        $totalAlumnos = \App\Models\Alumno::count();
        $totalMatriculados = \App\Models\Matricula::where('estado', 'activo')->count();
        $totalPersonal = \App\Models\Docente::count(); // O la tabla general de usuarios/empleados

        // --- LÓGICA DEL CALENDARIO INTERACTIVO PARA EL DOCENTE ---
        /** @var \App\Models\Usuario $usuario */
        $docente = Docente::where('usuario_id', $usuario->id)->first();
        
        $matrizHorario = [];
        $bloques = collect();
        $diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];
        $esquemaActivo = 'Regular'; 
        $asignaciones = collect();

        if ($docente) {
            $asignaciones = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'aula.modalidad', 'horarios.bloqueHorario'])
                ->where('docente_id', $docente->id)
                ->get();

            $modalidadesIds = $asignaciones->pluck('aula.modalidad_id')->unique();

            $bloques = BloqueHorario::whereIn('modalidad_id', $modalidadesIds)
                ->where('tipo_jornada', $esquemaActivo)
                ->orderBy('hora_inicio')
                ->get()
                ->unique('hora_inicio'); 

            foreach ($diasSemana as $dia) {
                $matrizHorario[$dia] = [];
                foreach ($bloques as $bloque) {
                    $matrizHorario[$dia][$bloque->hora_inicio] = null;
                }
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