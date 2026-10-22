<?php

namespace App\Console\Commands;

use App\Services\RespaldoService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Restaura la base de datos desde un respaldo.
 *
 * Uso:
 *   php artisan respaldos:restaurar respaldo_2026-10-20_153045.zip
 *   php artisan respaldos:restaurar respaldo_...zip --confirmar   (no interactivo)
 *
 * Doble verificación obligatoria (lección del incidente 15.6):
 *  1. Muestra la conexión de destino y exige la palabra RESTAURAR.
 *  2. El servicio crea un respaldo de seguridad del estado actual antes
 *     de aplicar la restauración.
 */
class RespaldoRestaurar extends Command
{
    protected $signature = 'respaldos:restaurar
                            {archivo : Nombre del archivo de respaldo (respaldo_AAAA-MM-DD_HHMMSS.zip)}
                            {--confirmar : Omitir la confirmación interactiva (usar solo en scripts confiables)}';

    protected $description = 'Restaura la base de datos desde un respaldo (OPERACIÓN DESTRUCTIVA)';

    public function handle(RespaldoService $respaldos): int
    {
        $archivo = (string) $this->argument('archivo');

        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║     RESTAURACIÓN DE BASE DE DATOS — SGA          ║');
        $this->info('╚══════════════════════════════════════════════════╝');

        // Validar existencia/validez del archivo y mostrar sus metadatos.
        try {
            $meta = $respaldos->leerCabecera($archivo);
        } catch (Throwable $e) {
            $this->error('  ✗ '.$e->getMessage());

            return self::FAILURE;
        }

        $conexion = $respaldos->estadoConexion();

        $this->line('  Respaldo      : '.$archivo);
        $this->line('  Generado      : '.($meta['fecha'] ?? '?'));
        $this->line('  Motivo        : '.($meta['motivo'] ?? '?'));
        $this->line('  Base original : '.($meta['base'] ?? '?').' @ '.($meta['host'] ?? '?'));
        $this->line('  Filas         : '.number_format((int) ($meta['filas'] ?? 0)));
        $this->newLine();
        $this->line('  ── DESTINO de la restauración ────────────────────');
        $this->line("  Motor : {$conexion['driver']}");
        $this->line("  Host  : {$conexion['host']}");
        $this->line("  Base  : {$conexion['base']}");
        $this->newLine();

        if (($meta['motor'] ?? '') !== $conexion['driver']) {
            $this->error("  ✗ El respaldo es del motor \"{$meta['motor']}\" y la conexión activa es \"{$conexion['driver']}\". Cancelado.");

            return self::FAILURE;
        }

        $this->warn('  ⚠ ESTA OPERACIÓN BORRA TODOS LOS DATOS ACTUALES y los sustituye');
        $this->warn('    por el contenido del respaldo. Se creará primero un respaldo');
        $this->warn('    de seguridad del estado actual.');
        $this->newLine();

        // Confirmación humana: escribir RESTAURAR.
        if (! $this->option('confirmar')) {
            $respuesta = $this->ask('  Escribe RESTAURAR (en mayúsculas) para continuar, o cualquier otra cosa para cancelar');

            if ($respuesta !== 'RESTAURAR') {
                $this->line('  Cancelado. No se modificó nada.');

                return self::SUCCESS;
            }
        }

        try {
            $resultado = $respaldos->restaurar($archivo);
        } catch (Throwable $e) {
            $this->error('  ✗ La restauración NO se aplicó: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("  ✓ Base de datos restaurada ({$resultado['filas']} filas).");
        $this->line('    Respaldo del estado previo: '.$resultado['respaldo_previo']);

        return self::SUCCESS;
    }
}
