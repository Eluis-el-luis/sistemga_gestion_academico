<?php

namespace App\Http\Controllers;

use App\Models\ApoyoPadres;
use App\Models\Aula;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApoyoPadresController extends Controller
{
    use AuthorizesRequests;

    /**
     * Seguimiento de apoyo de padres por aula y mes.
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', ApoyoPadres::class);

            $usuario = auth()->user();

            $aulas = Aula::with(['grado', 'anioEscolar'])
                ->whereHas('anioEscolar', fn ($q) => $q->where('activo', true))
                ->when($usuario->docente && !$usuario->hasRole(['Director', 'Subdirector']), function ($q) use ($usuario) {
                    $q->where('docente_guia_id', $usuario->docente->id);
                })
                ->get();

            $aulaSeleccionada = $request->query('aula_id', $aulas->first()->id ?? null);

            $registros = collect();
            if ($aulaSeleccionada) {
                $registros = ApoyoPadres::where('aula_id', $aulaSeleccionada)
                    ->orderBy('mes')
                    ->get();
            }

            return view('academico.apoyo-padres.index', compact('aulas', 'aulaSeleccionada', 'registros'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla la consulta de base de datos o permisos
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al intentar cargar el módulo de Apoyo de Padres. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Registra o actualiza el apoyo de padres de un aula en un mes.
     */
    public function store(Request $request)
    {
        // Se valida AFUERA del try-catch para mantener vivos los mensajes de validación del formulario
        $request->validate([
            'aula_id' => 'required|exists:aula,id',
            'mes' => 'required|date_format:Y-m',
            'cantidad_apoyan' => 'required|integer|min:0',
            'total_padres' => 'required|integer|min:1',
        ]);

        try {
            $this->authorize('create', ApoyoPadres::class);

            if ($request->cantidad_apoyan > $request->total_padres) {
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

        } catch (\Exception $e) {
            // CONTINGENCIA: Si la base de datos falla al intentar guardar
            return back()->withInput()->with('error', 'Hubo un problema al guardar el registro en la base de datos. Verifica la información e intenta de nuevo.');
        }
    }

    /**
     * Elimina un registro de apoyo de padres.
     */
    public function destroy(ApoyoPadres $apoyo)
    {
        try {
            $this->authorize('delete', $apoyo);
            
            $apoyo->delete();

            return back()->with('success', 'Registro de apoyo de padres eliminado.');

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla el borrado por algún problema interno de BD
            return back()->with('error', 'No se pudo eliminar el registro. Es posible que el sistema esté protegiendo datos asociados.');
        }
    }
}