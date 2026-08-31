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
        $this->authorize('malla.gestionar');

        // Ordenamiento estricto por modalidad e ID para que los bloques nunca cambien de lugar
        $grados = Grado::with(['mallaCurricular.asignatura', 'modalidad'])
                    ->orderBy('modalidad_id', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
                    
        $asignaturas = Asignatura::all();

        return view('academico.malla.index', compact('grados', 'asignaturas'));
    }

    public function store(Request $request)
    {
        $this->authorize('malla.gestionar');

        $request->validate([
            'grado_id' => 'required|exists:grado,id',
            'asignatura_id' => 'required|exists:asignatura,id',
            'horas_semanales_sugeridas' => 'required|numeric|min:1|max:40'
        ]);

        $grado = Grado::with('mallaCurricular')->findOrFail($request->grado_id);

        // 1. Verificación de duplicados
        $existe = MallaCurricular::where('grado_id', $request->grado_id)
                                 ->where('asignatura_id', $request->asignatura_id)
                                 ->first();

        if ($existe) {
            return back()->with('error', 'Error: Esta asignatura ya forma parte de la malla oficial de este grado.');
        }

        // 2. Verificación de TOPE DE HORAS
        // NOTA: Si aún no creas la columna 'horas_maximas_semanales' en la BD, usará 35 por defecto para no fallar.
        $limiteHoras = $grado->horas_maximas_semanales ?? 35; 
        
        $horasActuales = $grado->mallaCurricular->sum('horas_semanales_sugeridas');
        $horasNuevas = $request->horas_semanales_sugeridas;

        if (($horasActuales + $horasNuevas) > $limiteHoras) {
            $disponibles = $limiteHoras - $horasActuales;
            return back()->with('error', "No se puede añadir. El límite de este grado es {$limiteHoras}h semanales y solo quedan {$disponibles}h disponibles.");
        }

        // 3. Si todo está bien, guardamos
        MallaCurricular::create([
            'grado_id' => $request->grado_id,
            'asignatura_id' => $request->asignatura_id,
            'horas_semanales_sugeridas' => $request->horas_semanales_sugeridas,
            'activo' => true
        ]);

        return back()->with('success', 'Asignatura agregada a la plantilla oficial con éxito.');
    }

    public function destroy($id)
    {
        $this->authorize('malla.gestionar');
        
        $malla = MallaCurricular::findOrFail($id);
        $malla->delete();

        return back()->with('success', 'Asignatura removida de la plantilla oficial exitosamente.');
    }

    /**
     * Actualiza el límite de horas máximas semanales de un grado
     */
    public function actualizarHorasGrado(Request $request, Grado $grado)
    {
        $this->authorize('malla.gestionar');

        $request->validate([
            'horas_maximas_semanales' => 'required|integer|min:1|max:60'
        ]);

        $grado->update([
            'horas_maximas_semanales' => $request->horas_maximas_semanales
        ]);

        return back()->with('success', "Límite de horas para {$grado->nombre} actualizado a {$request->horas_maximas_semanales}h.");
    }
}