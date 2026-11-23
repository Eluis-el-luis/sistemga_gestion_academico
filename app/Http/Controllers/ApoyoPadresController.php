<?php

namespace App\Http\Controllers;

use App\Models\ApoyoPadres;
use App\Models\Aula;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApoyoPadresController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', ApoyoPadres::class);

            /** @var Usuario $usuario */
            $usuario = Auth::user();

            $aulas = Aula::with(['grado', 'anioEscolar'])
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->when($usuario->docente && !$usuario->hasAnyRole(['directora', 'subdirectora', 'coordinador', 'Director', 'Subdirector', 'Coordinador']), function ($q) use ($usuario) {
                    $q->where('docente_guia_id', $usuario->docente->id);
                })
                ->get();

            if ($aulas->isEmpty()) {
                return view('academico.apoyo-padres.index', [
                    'aulas' => collect(),
                    'aulaSeleccionada' => null,
                    'registros' => collect(),
                ]);
            }

            $aulaSeleccionada = $request->query('aula_id', $aulas->first()->id);
            $registros = $aulaSeleccionada
                ? ApoyoPadres::where('aula_id', $aulaSeleccionada)->orderBy('mes')->get()
                : collect();

            return view('academico.apoyo-padres.index', compact('aulas', 'aulaSeleccionada', 'registros'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error al intentar cargar el módulo de Apoyo de Padres.'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'aula_id' => ['required', 'integer', 'exists:aula,id'],
            'mes' => ['required', 'date_format:Y-m'],
            'cantidad_apoyan' => ['required', 'integer', 'min:0'],
            'total_padres' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->authorize('create', ApoyoPadres::class);

            if ((int) $request->cantidad_apoyan > (int) $request->total_padres) {
                return back()->with('error', 'La cantidad de padres que apoyan no puede superar el total de padres.');
            }

            ApoyoPadres::updateOrCreate(
                [
                    'aula_id' => $request->aula_id,
                    'mes' => $request->mes,
                ],
                [
                    'cantidad_apoyan' => $request->cantidad_apoyan,
                    'total_padres' => $request->total_padres,
                ]
            );

            return back()->with('success', 'Registro de apoyo de padres guardado correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un problema al guardar el registro de apoyo de padres.'));
        }
    }

    public function destroy(ApoyoPadres $apoyo)
    {
        try {
            $this->authorize('delete', $apoyo);
            $apoyo->delete();

            return back()->with('success', 'Registro de apoyo de padres eliminado.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el registro.'));
        }
    }
}