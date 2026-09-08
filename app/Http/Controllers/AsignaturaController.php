<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\Usuario $user */
        $user = auth()->user();
        
        // Bloqueo directo por rol en lugar de usar un Policy
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'No tienes permiso para modificar el catálogo del currículo.');
        }

        $asignaturas = Asignatura::orderBy('nombre', 'asc')->get();
        
        return view('academico.asignaturas.index', compact('asignaturas'));
    }

    public function store(Request $request)
    {
        // Limpiamos espacios vacíos accidentales al inicio o al final
        $request->merge(['nombre' => trim($request->nombre)]);

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    // Verificación insensible a mayúsculas/minúsculas para PostgreSQL
                    $existe = \App\Models\Asignatura::whereRaw('LOWER(nombre) = ?', [mb_strtolower($value, 'UTF-8')])->exists();
                    if ($existe) {
                        $fail('Esta asignatura ya está registrada en el catálogo.');
                    }
                },
            ]
        ]);

        Asignatura::create(['nombre' => $request->nombre]);

        return back()->with('success', 'Asignatura registrada correctamente.');
    }

    public function update(Request $request, Asignatura $asignatura)
    {
        $request->merge(['nombre' => trim($request->nombre)]);

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($asignatura) {
                    // Verificamos que no exista otro registro con el mismo nombre (ignorando mayúsculas) excluyendo el ID actual
                    $existe = \App\Models\Asignatura::whereRaw('LOWER(nombre) = ?', [mb_strtolower($value, 'UTF-8')])
                        ->where('id', '!=', $asignatura->id)
                        ->exists();
                    
                    if ($existe) {
                        $fail('Esta asignatura ya está registrada en el catálogo.');
                    }
                },
            ]
        ]);

        $asignatura->update(['nombre' => $request->nombre]);

        return back()->with('success', 'Asignatura actualizada correctamente.');
    }

    public function destroy(Asignatura $asignatura)
    {
        try {
            $asignatura->delete();
            return back()->with('success', 'Asignatura eliminada del catálogo.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'No puedes eliminar esta asignatura porque ya tiene calificaciones o está asignada a un docente.');
        }
    }
}
