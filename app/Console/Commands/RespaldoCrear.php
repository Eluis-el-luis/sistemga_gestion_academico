<?php

namespace App\Console\Commands;

use App\Services\RespaldoService;
use Illuminate\Console\Command;
use Throwable;

class RespaldoCrear extends Command
{
    protected $signature = 'respaldos:crear
                            {--motivo= : Motivo descriptivo registrado en la cabecera del respaldo}';

    protected $description = 'Genera un respaldo completo de la base de datos (zip con sentencias SQL)';

    public function handle(RespaldoService $respaldos): int
    {
        try {
            $conexion =$respaldos->estadoConexion();

            $this->info('==================================================');
            $this->info('        RESPALDO DE BASE DE DATOS - SGA           ');$this->info('==================================================');
            $this->line("  Motor : {$conexion['driver']}");
            $this->line("  Host  : {$conexion['host']}");
            $this->line("  Base  : {$conexion['base']}");
            $this->line('  Tablas: ' .$conexion['tablas']);

            if ($conexion['es_supabase']) {$this->warn('  Conexión a SUPABASE (base de datos real).');
            }

            $this->newLine();$this->line('  Generando respaldo...');

            $resultado = $respaldos->crearRespaldo($this->option('motivo') ?: 'cli-artisan');

            $tamanio = number_format($resultado['bytes'] / 1024, 1) . ' KB';
            $duracion = number_format($resultado['duracion_ms'] / 1000, 1) . ' s';

            $this->newLine();$this->info("  Respaldo generado: {$resultado['nombre']}");
            $this->line("    Tablas : {$resultado['tablas']}");
            $this->line('    Filas  : ' . number_format($resultado['filas']));$this->line("    Tamaño : {$tamanio}   ({$duracion})");
            $this->line('    Ubicación: ' . $resultado['ruta']);

            return self::SUCCESS;
        } catch (Throwable $e) {$this->error('  El respaldo FALLÓ: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}