<?php

namespace App\Http\Middleware;

use App\Models\AnioEscolar;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnioEscolarModificable
{
    private const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!in_array(strtoupper($request->method()), self::WRITE_METHODS, true)) {
                return $next($request);
            }

            $anioActivo = AnioEscolar::obtenerActivo();

            if (!$anioActivo) {
                return back()
                    ->withInput()
                    ->with('error', 'No hay un año escolar activo para realizar modificaciones.');
            }

            if ($anioActivo->es_cerrado) {
                return back()
                    ->withInput()
                    ->with('error', 'El año escolar activo se encuentra cerrado y es de solo lectura.');
            }

            return $next($request);
        } catch (\Throwable $e) {
            Log::error('Error en EnsureAnioEscolarModificable middleware', [
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'exception' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'No fue posible validar el estado del año escolar.');
        }
    }
}