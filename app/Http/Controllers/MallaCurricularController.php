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

        $existe = MallaCurricular::where('grado_id', $request->grado_id)
                                 ->where('asignatura_id', $request->asignatura_id)
                                 ->first();

        if ($existe) {
            return back()->with('error', 'Error: Esta asignatura ya forma parte de la malla oficial de este grado.');
        }

        $limiteHoras = $grado->horas_maximas_semanales ?? 35; 
        
        $horasActuales = $grado->mallaCurricular->sum('horas_semanales_sugeridas');
        $horasNuevas = $request->horas_semanales_sugeridas;

        if (($horasActuales + $horasNuevas) > $limiteHoras) {
            $disponibles = $limiteHoras - $horasActuales;
            return back()->with('error', "No se puede añadir. El límite de este grado es {$limiteHoras}h semanales y solo quedan {$disponibles}h disponibles.");
        }

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

    // 1. NUEVO: Edición rápida de horas por asignatura
    public function update(Request $request, $id)
    {
        $this->authorize('malla.gestionar');
        
        $request->validate([
            'horas_semanales_sugeridas' => 'required|numeric|min:1|max:40'
        ]);

        $malla = MallaCurricular::with('grado.mallaCurricular')->findOrFail($id);
        $grado = $malla->grado;

        $limiteHoras = $grado->horas_maximas_semanales ?? 35;
        // Calculamos las horas actuales excluyendo la materia que estamos editando
        $horasActualesSinEsta = $grado->mallaCurricular->where('id', '!=', $malla->id)->sum('horas_semanales_sugeridas');
        $horasNuevas = $request->horas_semanales_sugeridas;

        if (($horasActualesSinEsta + $horasNuevas) > $limiteHoras) {
            $disponibles = $limiteHoras - $horasActualesSinEsta;
            return back()->with('error', "No se puede editar. Solo quedan {$disponibles}h disponibles en este grado.");
        }

        $malla->update(['horas_semanales_sugeridas' => $horasNuevas]);
        
        return back()->with('success', 'Horas de la materia actualizadas correctamente.');
    }

    // 2. NUEVO: Clonación de Malla Completa
    public function clonarMalla(Request $request)
    {
        $this->authorize('malla.gestionar');

        $request->validate([
            'origen_grado_id' => 'required|exists:grado,id|different:destino_grado_id',
            'destino_grado_id' => 'required|exists:grado,id'
        ], [
            'origen_grado_id.different' => 'El grado origen y destino no pueden ser el mismo.'
        ]);

        $origen = Grado::with('mallaCurricular')->findOrFail($request->origen_grado_id);
        $destino = Grado::with('mallaCurricular')->findOrFail($request->destino_grado_id);

        if ($origen->mallaCurricular->isEmpty()) {
            return back()->with('error', 'El grado de origen no tiene materias configuradas.');
        }

        $materiasDestinoIds = $destino->mallaCurricular->pluck('asignatura_id')->toArray();
        $horasActualesDestino = $destino->mallaCurricular->sum('horas_semanales_sugeridas');
        $limiteHorasDestino = $destino->horas_maximas_semanales ?? 35;

        $materiasInsertar = [];
        $horasAAgregar = 0;

        foreach ($origen->mallaCurricular as $item) {
            // Solo preparamos las materias que el destino aún no tenga
            if (!in_array($item->asignatura_id, $materiasDestinoIds)) {
                $materiasInsertar[] = [
                    'grado_id' => $destino->id,
                    'asignatura_id' => $item->asignatura_id,
                    'horas_semanales_sugeridas' => $item->horas_semanales_sugeridas,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $horasAAgregar += $item->horas_semanales_sugeridas;
            }
        }

        if (empty($materiasInsertar)) {
            return back()->with('success', 'El grado destino ya contiene todas las materias del grado origen.');
        }

        if (($horasActualesDestino + $horasAAgregar) > $limiteHorasDestino) {
            return back()->with('error', "La clonación excede el límite de horas del grado destino.");
        }

        MallaCurricular::insert($materiasInsertar);

        return back()->with('success', count($materiasInsertar) . ' materias clonadas exitosamente.');
    }
}