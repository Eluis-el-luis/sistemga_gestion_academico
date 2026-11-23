<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Grado;
use App\Models\MallaCurricular;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MallaCurricularController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $this->authorize('malla.gestionar');

            $grados = Grado::with(['mallaCurricular.asignatura', 'modalidad'])
                ->orderBy('modalidad_id', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $asignaturas = Asignatura::orderBy('nombre')->get();

            return view('academico.malla.index', compact('grados', 'asignaturas'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar la Malla Curricular.'));
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated =$request->validate([
            'grado_id' => ['required', 'integer', 'exists:grado,id'],
            'asignatura_id' => ['required', 'integer', 'exists:asignatura,id'],
            'horas_semanales_sugeridas' => ['required', 'numeric', 'min:1', 'max:40'],
        ]);

        try {
            $this->authorize('malla.gestionar');

            $grado = Grado::with('mallaCurricular')->findOrFail($validated['grado_id']);

            $existe = MallaCurricular::where('grado_id',$validated['grado_id'])
                ->where('asignatura_id', $validated['asignatura_id'])
                ->exists();

            if ($existe) {
                return back()->withInput()
                    ->with('error', 'Esta asignatura ya forma parte de la malla oficial de este grado.');
            }

            $limiteHoras = (float) ($grado->horas_maximas_semanales ?? 35);
            $horasActuales = (float)$grado->mallaCurricular->sum('horas_semanales_sugeridas');
            $horasNuevas = (float)$validated['horas_semanales_sugeridas'];

            if (($horasActuales +$horasNuevas) > $limiteHoras) {$disponibles = max(0, $limiteHoras -$horasActuales);

                return back()->withInput()->with(
                    'error',
                    "No se puede añadir. El límite de este grado es {$limiteHoras}h semanales y solo quedan {$disponibles}h disponibles."
                );
            }

            MallaCurricular::create([
                'grado_id' => $validated['grado_id'],
                'asignatura_id' => $validated['asignatura_id'],
                'horas_semanales_sugeridas' => $horasNuevas,
                'activo' => true,
            ]);

            return back()->with('success', 'Asignatura agregada a la plantilla oficial con éxito.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $this->formatearError($e, 'No fue posible agregar la asignatura a la malla.'));
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $this->authorize('malla.gestionar');

            $malla = MallaCurricular::findOrFail($id);$malla->delete();

            return back()->with('success', 'Asignatura removida de la plantilla oficial exitosamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible remover la asignatura de la malla.'));
        }
    }

    public function actualizarHorasGrado(Request $request, Grado$grado): RedirectResponse
    {
        $validated =$request->validate([
            'horas_maximas_semanales' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        try {
            $this->authorize('malla.gestionar');

            $grado->update([
                'horas_maximas_semanales' => (int) $validated['horas_maximas_semanales'],
            ]);

            return back()->with(
                'success',
                "Límite de horas para {$grado->nombre} actualizado a {$validated['horas_maximas_semanales']}h."
            );
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $this->formatearError($e, 'No fue posible actualizar el límite de horas del grado.'));
        }
    }

    public function update(Request $request,$id): RedirectResponse
    {
        $validated =$request->validate([
            'horas_semanales_sugeridas' => ['required', 'numeric', 'min:1', 'max:40'],
        ]);

        try {
            $this->authorize('malla.gestionar');

            $malla = MallaCurricular::with('grado.mallaCurricular')->findOrFail($id);
            $grado =$malla->grado;

            $limiteHoras = (float) ($grado?->horas_maximas_semanales ?? 35);
            $horasActualesSinEsta = (float) ($grado?->mallaCurricular?->where('id', '!=', $malla->id)->sum('horas_semanales_sugeridas') ?? 0);
            $horasNuevas = (float)$validated['horas_semanales_sugeridas'];

            if (($horasActualesSinEsta +$horasNuevas) > $limiteHoras) {$disponibles = max(0, $limiteHoras -$horasActualesSinEsta);

                return back()->withInput()
                    ->with('error', "No se puede editar. Solo quedan {$disponibles}h disponibles en este grado.");
            }

            $malla->update(['horas_semanales_sugeridas' =>$horasNuevas]);

            return back()->with('success', 'Horas de la materia actualizadas correctamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $this->formatearError($e, 'No fue posible editar las horas de la asignatura.'));
        }
    }

    public function clonarMalla(Request $request): RedirectResponse
    {
        $validated =$request->validate([
            'origen_grado_id' => ['required', 'integer', 'exists:grado,id', 'different:destino_grado_id'],
            'destino_grado_id' => ['required', 'integer', 'exists:grado,id'],
        ], [
            'origen_grado_id.different' => 'El grado origen y destino no pueden ser el mismo.',
        ]);

        try {
            $this->authorize('malla.gestionar');

            $origen = Grado::with('mallaCurricular')->findOrFail($validated['origen_grado_id']);
            $destino = Grado::with('mallaCurricular')->findOrFail($validated['destino_grado_id']);

            if ($origen->mallaCurricular->isEmpty()) {
                return back()->with('error', 'El grado de origen no tiene materias configuradas.');
            }

            $materiasDestinoIds =$destino->mallaCurricular->pluck('asignatura_id')->all();
            $horasActualesDestino = (float)$destino->mallaCurricular->sum('horas_semanales_sugeridas');
            $limiteHorasDestino = (float) ($destino->horas_maximas_semanales ?? 35);

            $materiasInsertar = [];$horasAAgregar = 0.0;
            $now = now();

            foreach ($origen->mallaCurricular as$item) {
                if (!in_array($item->asignatura_id,$materiasDestinoIds, true)) {
                    $horasItem = (float)$item->horas_semanales_sugeridas;
                    $materiasInsertar[] = [
                        'grado_id' => $destino->id,
                        'asignatura_id' => $item->asignatura_id,
                        'horas_semanales_sugeridas' => $horasItem,
                        'activo' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $horasAAgregar +=$horasItem;
                }
            }

            if ($materiasInsertar === []) {
                return back()->with('success', 'El grado destino ya contiene todas las materias del grado origen.');
            }

            if (($horasActualesDestino + $horasAAgregar) >$limiteHorasDestino) {
                return back()->with('error', 'La clonación excede el límite de horas del grado destino.');
            }

            DB::transaction(function () use ($materiasInsertar) {
                MallaCurricular::insert($materiasInsertar);
            });

            return back()->with('success', count($materiasInsertar) . ' materias clonadas exitosamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible completar la clonación de la malla curricular.'));
        }
    }
}