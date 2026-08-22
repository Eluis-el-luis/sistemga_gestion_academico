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
        $totalAlumnos = Alumno::count();
        $totalDocentes = Docente::count();
        
        $avisos = DB::table('aviso')
                    ->join('usuario', 'aviso.autor_id', '=', 'usuario.id')
                    ->select('aviso.*', 'usuario.nombre_completo as autor_nombre')
                    ->where('aviso.activo', true)
                    ->orderBy('aviso.created_at', 'desc')
                    ->take(5) // Subimos a 5 para que vean un buen historial
                    ->get();

        return view('dashboard', compact('totalAlumnos', 'totalDocentes', 'avisos'));
    }

    public function storeAviso(Request $request)
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // 🔒 BLINDAJE: Solo Director o Subdirector
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'No autorizado para publicar avisos.');
        }

        $request->validate([
            'titulo' => 'required|string|max:120',
            'mensaje' => 'required|string|max:1000',
        ]);

        DB::table('aviso')->insert([
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'autor_id' => Auth::id(),
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Aviso publicado correctamente.');
    }

    // NUEVO MÉTODO PARA ACTUALIZAR
    public function updateAviso(Request $request, $id)
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // 🔒 BLINDAJE: Solo Director o Subdirector
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'No autorizado para publicar avisos.');
        }

        $request->validate([
            'titulo' => 'required|string|max:120',
            'mensaje' => 'required|string|max:1000',
        ]);

        DB::table('aviso')->where('id', $id)->update([
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Aviso actualizado correctamente.');
    }

    // NUEVO MÉTODO PARA ELIMINAR
    public function destroyAviso($id)
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // 🔒 BLINDAJE: Solo Director o Subdirector
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'No autorizado para publicar avisos.');
        }

        DB::table('aviso')->where('id', $id)->delete();

        return redirect()->route('dashboard')->with('success', 'Aviso eliminado del sistema.');
    }
}