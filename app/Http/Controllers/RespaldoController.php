<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\RespaldoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\View\View;
use Throwable;

class RespaldoController extends Controller
{
    public function __construct(
        protected RespaldoService $respaldos
    ) {}

    public function index(): View|RedirectResponse
    {
        /** @var Usuario|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            abort(403, 'Solo la Dirección puede acceder al módulo de respaldos.');
        }

        try {
            $conexion = $this->respaldos->estadoConexion();
            $lista = $this->respaldos->listar();

            return view('academico.respaldos.index', compact('conexion', 'lista'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No fue posible leer el estado de los respaldos.'));
        }
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            abort(403, 'Solo la Dirección puede generar respaldos.');
        }

        $validated = $request->validate([
            'motivo' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $resultado = $this->respaldos->crearRespaldo($validated['motivo'] ?? 'manual-desde-interfaz');
            $tamanio = number_format($resultado['bytes'] / 1024, 1) . ' KB';
            $duracion = number_format($resultado['duracion_ms'] / 1000, 1) . ' s';

            return back()->with(
                'success',
                "Respaldo generado correctamente: {$resultado['tablas']} tablas, "
                . number_format($resultado['filas']) . " filas ({$tamanio} en {$duracion})."
            );
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No fue posible completar el respaldo.'));
        }
    }

    public function descargar(string $nombre): BinaryFileResponse|RedirectResponse
    {
        /** @var Usuario|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            abort(403, 'Solo la Dirección puede descargar respaldos.');
        }

        try {
            $ruta = $this->respaldos->rutaValida($nombre);

            return response()->download($ruta, $nombre);
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No fue posible descargar el respaldo.'));
        }
    }

    public function destroy(string $nombre): RedirectResponse
    {
        /** @var Usuario|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['directora', 'Director'])) {
            abort(403, 'Solo la Directora puede eliminar respaldos.');
        }

        try {
            $this->respaldos->eliminar($nombre);

            return back()->with('success', 'Respaldo eliminado.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No fue posible eliminar el respaldo.'));
        }
    }

    public function restaurar(Request $request, string $nombre): RedirectResponse
    {
        /** @var Usuario|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['directora', 'Director'])) {
            abort(403, 'Solo la Directora puede restaurar respaldos.');
        }

        $validated = $request->validate([
            'confirmacion' => ['required', 'string'],
        ]);

        if (trim($validated['confirmacion']) !== 'RESTAURAR') {
            return back()->with('error', 'La confirmación no coincide. Debe escribirse exactamente la palabra RESTAURAR.');
        }

        try {
            $resultado = $this->respaldos->restaurar($nombre);

            return back()->with(
                'success',
                "Base de datos restaurada desde {$resultado['archivo']} ("
                . number_format($resultado['filas']) . ' filas). Respaldo del estado previo: '
                . $resultado['respaldo_previo'] . '.'
            );
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'La restauración no se aplicó.'));
        }
    }
}