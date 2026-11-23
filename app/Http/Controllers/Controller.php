<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

abstract class Controller extends BaseController
{
    protected function formatearError(\Throwable $e, string $mensajeAmigable): string
    {
        Log::error($mensajeAmigable . ' | Error: ' . $e->getMessage(), [
            'archivo' => $e->getFile(),
            'linea' => $e->getLine(),
            'usuario' => Auth::id(),
        ]);

        if (config('app.debug')) {
            $archivoCorto = basename($e->getFile());

            return "{$mensajeAmigable} — [Detalle Técnico: {$e->getMessage()} en {$archivoCorto} (Línea {$e->getLine()})]";
        }

        return $mensajeAmigable;
    }
}