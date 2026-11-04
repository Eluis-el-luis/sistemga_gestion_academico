<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnioEscolar\StoreAnioEscolarRequest;
use App\Http\Requests\AnioEscolar\UpdateAnioEscolarRequest;
use App\Http\Requests\AnioEscolar\CambiarEstadoAnioEscolarRequest;
use App\Models\AnioEscolar;
use App\Services\AnioEscolarService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnioEscolarController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AnioEscolarService $anioEscolarService
    ) {
        $this->authorizeResource(AnioEscolar::class, 'anioEscolar');
    }

    /**
     * Lista todos los años escolares con sus estados y estadísticas.
     */
    public function index(): View
    {
        $aniosEscolares = AnioEscolar::ordenado()
            ->withCount(['aulas', 'matriculas', 'cortesEvaluativos'])
            ->get();

        $anioActivo = AnioEscolar::obtenerActivo();

        return view('academico.anios-escolares.index', compact('aniosEscolares', 'anioActivo'));
    }

    /**
     * Muestra el formulario para crear un nuevo año escolar.
     */
    public function create(): View
    {
        return view('academico.anios-escolares.create');
    }

    /**
     * Almacena un nuevo año escolar en Planificación.
     */
    public function store(StoreAnioEscolarRequest $request): RedirectResponse
    {
        $anioEscolar = $this->anioEscolarService->crear(
            $request->validated(),
            Auth::user()
        );

        return redirect()
            ->route('academico.anios-escolares.index')
            ->with('success', "Año escolar «{$anioEscolar->nombre}» creado correctamente en estado Planificación.");
    }

    /**
     * Muestra el detalle de un año escolar con validaciones de cierre.
     */
    public function show(AnioEscolar $anioEscolar): View
    {
        $reporteValidaciones = $this->anioEscolarService->obtenerReporteValidacionesCierre($anioEscolar);
        $transiciones = $anioEscolar->getTransicionesPermitidas();

        return view('academico.anios-escolares.show', compact(
            'anioEscolar',
            'reporteValidaciones',
            'transiciones'
        ));
    }

    /**
     * Muestra el formulario para editar un año escolar (solo Planificación).
     */
    public function edit(AnioEscolar $anioEscolar): View
    {
        if ($anioEscolar->es_cerrado) {
            abort(403, 'No se puede editar un año escolar cerrado.');
        }

        return view('academico.anios-escolares.edit', compact('anioEscolar'));
    }

    /**
     * Actualiza los datos básicos de un año escolar.
     */
    public function update(UpdateAnioEscolarRequest $request, AnioEscolar $anioEscolar): RedirectResponse
    {
        $anioEscolar = $this->anioEscolarService->actualizar(
            $anioEscolar,
            $request->validated(),
            Auth::user()
        );

        return redirect()
            ->route('academico.anios-escolares.show', $anioEscolar)
            ->with('success', 'Año escolar actualizado correctamente.');
    }

    /**
     * Cambia el estado del año escolar (Activar / Planificación / Cerrar).
     */
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Elimina un año escolar (solo Planificación sin dependencias).
     */
    public function destroy(AnioEscolar $anioEscolar): RedirectResponse
    {
        $this->anioEscolarService->eliminar($anioEscolar, Auth::user());

        return redirect()
            ->route('academico.anios-escolares.index')
            ->with('success', 'Año escolar eliminado correctamente.');
    }

    /**
     * API: Obtiene el año escolar activo actual.
     */
    public function activo(): \Illuminate\Http\JsonResponse
    {
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
    }

    /**
     * API: Obtiene reporte de validaciones para cierre (para modal/confirmación).
     */
    public function validacionesCierre(AnioEscolar $anioEscolar): \Illuminate\Http\JsonResponse
    {
        $reporte = $this->anioEscolarService->obtenerReporteValidacionesCierre($anioEscolar);

        return response()->json($reporte);
    }
}