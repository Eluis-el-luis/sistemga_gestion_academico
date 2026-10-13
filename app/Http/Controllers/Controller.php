<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

abstract class Controller
{
    /**
     * Registra el error real en storage/logs/laravel.log y devuelve el detalle
     * técnico solo si APP_DEBUG=true (entorno local).
     */
    protected function formatearError(\Throwable $e, string $mensajeAmigable): string
    {
        Log::error($mensajeAmigable . ' | Error: ' . $e->getMessage(), [
            'archivo' => $e->getFile(),
            'linea'   => $e->getLine(),
            'usuario' => auth()->id(),
        ]);

        if (config('app.debug')) {
            $archivoCorto = basename($e->getFile());
            return "{$mensajeAmigable} — [Detalle Técnico: {$e->getMessage()} en {$archivoCorto} (Línea {$e->getLine()})]";
        }

        return $mensajeAmigable;
    }
}