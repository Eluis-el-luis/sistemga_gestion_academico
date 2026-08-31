<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // 1. Datos Globales (Transversales a todos los roles)
        $avisos = DB::table('aviso')
            ->join('usuario', 'aviso.autor_id', '=', 'usuario.id')
            ->select('aviso.*', 'usuario.nombre_completo as autor_nombre')
            ->where('aviso.activo', true)
            ->orderBy('aviso.created_at', 'desc')
            ->take(5)
            ->get();

        // 2. Enrutamiento por Roles (Director de Tráfico)
        if ($user->hasAnyRole(['Director', 'Subdirector'])) {
            $totalAlumnos = \App\Models\Alumno::count();
            $totalDocentes = \App\Models\Docente::count();
            // (Aquí Luis agregará las consultas para las gráficas que tenías antes)
            return view('dashboard.direccion.index', compact('avisos', 'totalAlumnos', 'totalDocentes'));
        }

        if ($user->hasRole('Docente por Asignatura')) {
            $docenteId = $user->docente->id ?? null;
            $horarios = collect();
            $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

            if ($docenteId) {
                $horarios = \App\Models\Horario::with([
                        'bloqueHorario', 
                        'aulaAsignaturaDocente.asignatura', 
                        'aulaAsignaturaDocente.aula.grado', 
                        'aulaAsignaturaDocente.aula.modalidad'
                    ])
                    ->whereHas('aulaAsignaturaDocente', function($q) use ($docenteId) {
                        $q->where('docente_id', $docenteId);
                    })
                    ->get()
                    ->sortBy(fn($horario) => $horario->bloqueHorario->hora_inicio)
                    ->groupBy('dia_semana');
            }
            return view('dashboard.maestro_asignatura.index', compact('avisos', 'horarios', 'diasSemana'));
        }

        // Retorno por defecto si el rol aún no tiene carpeta propia
        return view('dashboard', compact('avisos'));
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