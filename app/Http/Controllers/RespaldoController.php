<?php

namespace App\Http\Controllers;

use App\Services\RespaldoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Módulo de Respaldos de la Base de Datos (Dirección).
 *
 * Creado tras el incidente de Oct 2026 donde se perdió el contenido de la
 * base de datos de Supabase y solo existían las migraciones para recuperar
 * la estructura (ver Contexto.md, secciones 15.6 y 16).
 *
 * Acceso: Director y Subdirector pueden ver, crear y descargar respaldos.
 * Eliminar y RESTAURAR es exclusivo del Director, con confirmación escrita.
 */
class RespaldoController extends Controller
{
    public function __construct(protected RespaldoService $respaldos) {}

    /** Pantalla principal: estado de la conexión y lista de respaldos. */
    public function index()
    {
        if (! auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'Solo la Dirección puede acceder al módulo de respaldos.');
        }

        try {
            $conexion = $this->respaldos->estadoConexion();
            $lista = $this->respaldos->listar();
        } catch (Throwable $e) {
            Log::error('No se pudo inicializar el módulo de respaldos', ['error' => $e->getMessage()]);

            return back()->with('error', 'No se pudo leer el estado de los respaldos: '.$e->getMessage());
        }

        return view('academico.respaldos.index', compact('conexion', 'lista'));
    }

    /** Genera un nuevo respaldo completo de la base de datos. */
    public function store(Request $request)
    {
        if (! auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'Solo la Dirección puede generar respaldos.');
        }

        $request->validate([
            'motivo' => 'nullable|string|max:100',
        ]);

        try {
            $resultado = $this->respaldos->crearRespaldo($request->input('motivo') ?: 'manual-desde-interfaz');
        } catch (Throwable $e) {
            Log::error('Fallo la generación de un respaldo', ['error' => $e->getMessage()]);

            return back()->with('error', 'No se pudo completar el respaldo: '.$e->getMessage());
        }

        $tamanio = number_format($resultado['bytes'] / 1024, 1).' KB';
        $duracion = number_format($resultado['duracion_ms'] / 1000, 1).' s';

        return back()->with(
            'success',
            "Respaldo generado correctamente: {$resultado['tablas']} tablas, ".
            number_format($resultado['filas'])." filas ({$tamanio} en {$duracion})."
        );
    }

    /** Descarga el archivo .zip del respaldo. */
    public function descargar(string $nombre)
    {
        if (! auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'Solo la Dirección puede descargar respaldos.');
        }

        $ruta = $this->respaldos->rutaValida($nombre);

        return response()->download($ruta, $nombre);
    }

    /** Elimina un respaldo del disco (solo Director). */
    public function destroy(string $nombre)
    {
        if (! auth()->user()->hasRole('Director')) {
            abort(403, 'Solo el Director puede eliminar respaldos.');
        }

        try {
            $this->respaldos->eliminar($nombre);
        } catch (Throwable $e) {
            return back()->with('error', 'No se pudo eliminar el respaldo: '.$e->getMessage());
        }

        return back()->with('success', 'Respaldo eliminado.');
    }

    /**
     * RESTAURA la base de datos desde un respaldo (solo Director).
     *
     * Requiere que el usuario escriba la palabra "RESTAURAR" como
     * confirmación. El servicio crea además un respaldo de seguridad del
     * estado actual antes de aplicar la restauración.
     */
    public function restaurar(Request $request, string $nombre)
    {
        if (! auth()->user()->hasRole('Director')) {
            abort(403, 'Solo el Director puede restaurar respaldos.');
        }

        $request->validate([
            'confirmacion' => ['required', 'string'],
        ]);

        if (trim($request->input('confirmacion')) !== 'RESTAURAR') {
            return back()->with('error', 'La confirmación no coincide. Debe escribirse exactamente la palabra RESTAURAR.');
        }

        try {
            $resultado = $this->respaldos->restaurar($nombre);
        } catch (Throwable $e) {
            return back()->with('error', 'La restauración no se aplicó: '.$e->getMessage());
        }

        return back()->with(
            'success',
            "Base de datos restaurada desde {$resultado['archivo']} (".
            number_format($resultado['filas']).' filas). Respaldo del estado previo: '.
            $resultado['respaldo_previo'].'.'
        );
    }
}
