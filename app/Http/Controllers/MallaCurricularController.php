<?php

namespace App\Http\Controllers;

use App\Models\MallaCurricular;
use App\Models\Grado;
use App\Models\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MallaCurricularController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            $this->authorize('malla.gestionar');

            $grados = Grado::with(['mallaCurricular.asignatura', 'modalidad'])
                        ->orderBy('modalidad_id', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();
                        
            $asignaturas = Asignatura::all();

            return view('academico.malla.index', compact('grados', 'asignaturas'));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla la carga de las relaciones
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error inesperado al intentar cargar la Malla Curricular.');
        }
    }

    public function store(Request $request)
    {
        // Validación AFUERA del try-catch para mantener alertas del formulario
        $request->validate([
            'grado_id' => 'required|exists:grado,id',
            'asignatura_id' => 'required|exists:asignatura,id',
            'horas_semanales_sugeridas' => 'required|numeric|min:1|max:40'
        ]);

        try {
            $this->authorize('malla.gestionar');

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

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Falla de inserción
            return back()->withInput()->with('error', 'Ocurrió un problema técnico al intentar agregar la asignatura a la malla.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->authorize('malla.gestionar');
            
            $malla = MallaCurricular::findOrFail($id);
            $malla->delete();

            return back()->with('success', 'Asignatura removida de la plantilla oficial exitosamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Protege ante borrados bloqueados por la BD
            return back()->with('error', 'No se pudo remover la asignatura. Es posible que esté en uso por clases activas en este grado.');
        }
    }

    public function actualizarHorasGrado(Request $request, Grado $grado)
    {
        $request->validate([
            'horas_maximas_semanales' => 'required|integer|min:1|max:60'
        ]);

        try {
            $this->authorize('malla.gestionar');

            $grado->update([
                'horas_maximas_semanales' => $request->horas_maximas_semanales
            ]);

            return back()->with('success', "Límite de horas para {$grado->nombre} actualizado a {$request->horas_maximas_semanales}h.");

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'Hubo un error al intentar actualizar el límite de horas.');
        }
    }

    // 1. NUEVO: Edición rápida de horas por asignatura
    public function update(Request $request, $id)
    {
        $request->validate([
            'horas_semanales_sugeridas' => 'required|numeric|min:1|max:40'
        ]);

        try {
            $this->authorize('malla.gestionar');
            
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

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'Ocurrió un problema técnico al editar las horas de esta materia.');
        }
    }

    // 2. NUEVO: Clonación de Malla Completa
    public function clonarMalla(Request $request)
    {
        $request->validate([
            'origen_grado_id' => 'required|exists:grado,id|different:destino_grado_id',
            'destino_grado_id' => 'required|exists:grado,id'
        ], [
            'origen_grado_id.different' => 'El grado origen y destino no pueden ser el mismo.'
        ]);

        DB::beginTransaction();
        try {
            $this->authorize('malla.gestionar');

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

            DB::commit();
            return back()->with('success', count($materiasInsertar) . ' materias clonadas exitosamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            DB::rollBack();
            // CONTINGENCIA: Si falla la clonación, se cancela todo
            return back()->with('error', 'Ocurrió un error inesperado durante la clonación. El proceso ha sido cancelado por seguridad para proteger sus datos.');
        }
    }
}