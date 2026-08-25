<?php

namespace App\Http\Controllers;

use App\Models\MallaCurricular;
use App\Models\Grado;
use App\Models\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MallaCurricularController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // Solo Dirección y Subdirección tienen este permiso
        $this->authorize('malla.gestionar');

        // Traemos todos los grados junto con las asignaturas que ya tienen en su malla
        $grados = Grado::with(['mallaCurricular.asignatura'])->get();
        
        // Traemos el catálogo completo de asignaturas para el formulario de agregar
        $asignaturas = Asignatura::all();

        return view('academico.malla.index', compact('grados', 'asignaturas'));
    }

    public function store(Request $request)
    {
        $this->authorize('malla.gestionar');

        $request->validate([
            'grado_id' => 'required|exists:grado,id',
            'asignatura_id' => 'required|exists:asignatura,id',
            
        ]);

        // Evitar duplicados: no meter la misma materia dos veces al mismo grado
        $existe = MallaCurricular::where('grado_id', $request->grado_id)
                                 ->where('asignatura_id', $request->asignatura_id)
                                 ->first();

        if ($existe) {
            return back()->with('error', 'Error: Esta asignatura ya forma parte de la malla curricular de ese grado.');
        }

        MallaCurricular::create([
            'grado_id' => $request->grado_id,
            'asignatura_id' => $request->asignatura_id,
           
            'activo' => true
        ]);

        return back()->with('success', 'Asignatura agregada a la plantilla oficial del grado con éxito.');
    }

    public function destroy($id)
    {
        $this->authorize('malla.gestionar');
        
        $malla = MallaCurricular::findOrFail($id);
        $malla->delete();

        return back()->with('success', 'Asignatura removida de la plantilla oficial exitosamente.');
    }
}