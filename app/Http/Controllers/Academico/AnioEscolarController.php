<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnioEscolar\CambiarEstadoAnioEscolarRequest;
use App\Http\Requests\AnioEscolar\StoreAnioEscolarRequest;
use App\Http\Requests\AnioEscolar\UpdateAnioEscolarRequest;
use App\Models\AnioEscolar;
use App\Services\AnioEscolarService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnioEscolarController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AnioEscolarService $anioEscolarService
    ) {
        $this->authorizeResource(AnioEscolar::class, 'anioEscolar');
    }

    public function index(): View|RedirectResponse
    {
        try {
            $aniosEscolares = AnioEscolar::ordenado()
                ->withCount(['aulas', 'matriculas', 'cortesEvaluativos'])
                ->get();

            $anioActivo = AnioEscolar::obtenerActivo();

            return view('academico.anios-escolares.index', compact('aniosEscolares', 'anioActivo'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'No fue posible cargar los años escolares.'));
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            return view('academico.anios-escolares.create');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.anios-escolares.index')->with('error', $this->formatearError($e, 'No se pudo abrir el formulario de año escolar.'));
        }
    }

    public function store(StoreAnioEscolarRequest $request): RedirectResponse
    {
        try {
            $anioEscolar = $this->anioEscolarService->crear($request->validated(), Auth::user());

            return redirect()
                ->route('academico.anios-escolares.index')
                ->with('success', "Año escolar «{$anioEscolar->nombre}» creado correctamente en estado Planificación.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'No fue posible crear el año escolar.'));
        }
    }

    public function show(AnioEscolar $anioEscolar): View|RedirectResponse
    {
        try {
            $reporteValidaciones = $this->anioEscolarService->obtenerReporteValidacionesCierre($anioEscolar);
            $transiciones = $anioEscolar->getTransicionesPermitidas();

            return view('academico.anios-escolares.show', compact(
                'anioEscolar',
                'reporteValidaciones',
                'transiciones'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.anios-escolares.index')->with('error', $this->formatearError($e, 'No fue posible mostrar el detalle del año escolar.'));
        }
    }

    public function edit(AnioEscolar $anioEscolar): View|RedirectResponse
    {
        if ($anioEscolar->es_cerrado) {
            abort(403, 'No se puede editar un año escolar cerrado.');
        }

        return view('academico.anios-escolares.edit', compact('anioEscolar'));
    }

    public function update(UpdateAnioEscolarRequest $request, AnioEscolar $anioEscolar): RedirectResponse
    {
        try {
            $this->anioEscolarService->actualizar($anioEscolar, $request->validated(), Auth::user());

            return redirect()
                ->route('academico.anios-escolares.show', $anioEscolar)
                ->with('success', 'Año escolar actualizado correctamente.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'No fue posible actualizar el año escolar.'));
        }
    }

    public function cambiarEstado(CambiarEstadoAnioEscolarRequest $request, AnioEscolar $anioEscolar): RedirectResponse
    {
        $estado = $request->validated('estado');
        $observaciones = $request->validated('observaciones_cierre');

        try {
            match ($estado) {
                AnioEscolar::ESTADO_ACTIVO => $this->anioEscolarService->activar($anioEscolar, Auth::user()),
                AnioEscolar::ESTADO_PLANIFICACION => $this->anioEscolarService->pasarAPlanificacion($anioEscolar, Auth::user()),
                AnioEscolar::ESTADO_CERRADO => $this->anioEscolarService->cerrar($anioEscolar, Auth::user(), [
                    'observaciones_cierre' => $observaciones,
                ]),
                default => throw new \InvalidArgumentException("Estado no válido: {$estado}"),
            };

            $mensajes = [
                AnioEscolar::ESTADO_ACTIVO => 'Año escolar activado correctamente. Es ahora el año en operaciones.',
                AnioEscolar::ESTADO_PLANIFICACION => 'Año escolar pasado a Planificación.',
                AnioEscolar::ESTADO_CERRADO => 'Año escolar cerrado definitivamente. El historial académico es ahora inmutable.',
            ];

            return redirect()
                ->route('academico.anios-escolares.show', $anioEscolar)
                ->with('success', $mensajes[$estado] ?? 'Estado cambiado correctamente.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'No fue posible cambiar el estado del año escolar.'));
        }
    }

    public function destroy(AnioEscolar $anioEscolar): RedirectResponse
    {
        try {
            $this->anioEscolarService->eliminar($anioEscolar, Auth::user());

            return redirect()
                ->route('academico.anios-escolares.index')
                ->with('success', 'Año escolar eliminado correctamente.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No fue posible eliminar el año escolar.'));
        }
    }

    public function activo(): JsonResponse
    {
        try {
            $anioActivo = AnioEscolar::obtenerActivo();

            if (!$anioActivo) {
                return response()->json([
                    'activo' => false,
                    'mensaje' => 'No hay año escolar activo.',
                ], 404);
            }

            return response()->json([
                'activo' => true,
                'anio_escolar' => $anioActivo->load('cortesEvaluativos'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'activo' => false,
                'mensaje' => $this->formatearError($e, 'Error al consultar el año escolar activo.'),
            ], 500);
        }
    }

    public function validacionesCierre(AnioEscolar $anioEscolar): JsonResponse
    {
        try {
            return response()->json($this->anioEscolarService->obtenerReporteValidacionesCierre($anioEscolar));
        } catch (\Throwable $e) {
            return response()->json([
                'mensaje' => $this->formatearError($e, 'Error al consultar validaciones de cierre.'),
            ], 500);
        }
    }
}