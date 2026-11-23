<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Throwable;
use ZipArchive;

class RespaldoService
{
    public const MARCA = 'SGA-RESPALDO';
    public const VERSION_FORMATO = 1;
    public const PATRON_NOMBRE = '/^respaldo_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{6}(_[0-9]+)?\.zip$/';

    protected $pdo;
    protected string $driver;

    public function crearRespaldo(?string $motivo = null): array
    {
        $inicio = microtime(true);

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $this->pdo = DB::connection()->getPdo();
        $this->driver = DB::connection()->getDriverName();

        $tablas = $this->obtenerTablasOrdenadas();
        $excluir = config('respaldos.excluir_tablas', []);
        $tablas = array_values(array_diff($tablas, $excluir));

        $nombre = 'respaldo_' . now()->format('Y-m-d_His') . '.zip';
        $base = substr($nombre, 0, -4);
        $secuencia = 2;
        while (file_exists($this->directorio() . DIRECTORY_SEPARATOR . $nombre)) {
            $nombre = "{$base}_{$secuencia}.zip";
            $secuencia++;
        }

        $rutaZip = $this->directorio() . DIRECTORY_SEPARATOR . $nombre;
        $rutaSql = substr($rutaZip, 0, -4) . '.sql';

        $filasTotal = 0;
        $manejador = fopen($rutaSql, 'w');

        try {
            fwrite($manejador, $this->cabeceraSql($motivo, count($tablas)));
            fwrite($manejador, "BEGIN;\n\n");

            fwrite($manejador, "-- ===== LIMPIEZA (orden inverso de dependencias) =====\n");
            foreach (array_reverse($tablas) as $tabla) {
                fwrite($manejador, 'DELETE FROM ' . $this->identificador($tabla) . ";\n");
            }

            fwrite($manejador, "\n-- ===== DATOS (orden de dependencias) =====\n");
            foreach ($tablas as $tabla) {
                $filasTotal += $this->volcarTabla($manejador, $tabla);
            }

            if ($this->driver === 'pgsql') {
                fwrite($manejador, "\n-- ===== SECUENCIAS =====\n");
                foreach ($tablas as $tabla) {
                    foreach ($this->columnasSerial($tabla) as $columna) {
                        $tablaCita = str_replace('"', '', $this->identificador($tabla, false));
                        fwrite($manejador, sprintf(
                            "SELECT setval(pg_get_serial_sequence('public.%s', '%s'), COALESCE((SELECT MAX(%s) FROM %s), 0) + 1, false);\n",
                            $tablaCita,
                            $columna,
                            $this->identificador($columna),
                            $this->identificador($tabla)
                        ));
                    }
                }
            }

            fwrite($manejador, "\nCOMMIT;\n");
            fwrite($manejador, sprintf("\n-- FIN DEL RESPALDO — %s filas — generado por el SGA.\n", number_format($filasTotal, 0, '.', ',')));
        } finally {
            fclose($manejador);
        }

        $this->parcharTotalFilas($rutaSql, $filasTotal);

        $zip = new ZipArchive;
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($rutaSql);
            throw new \RuntimeException("No fue posible crear el archivo de respaldo: {$rutaZip}");
        }
        $zip->addFile($rutaSql, basename($rutaSql));
        $zip->close();
        @unlink($rutaSql);

        $this->aplicarRetencion();

        $bytes = filesize($rutaZip);
        $duracionMs = (int) round((microtime(true) - $inicio) * 1000);

        Log::info('Respaldo de base de datos creado', compact('nombre', 'tablas', 'filasTotal'));

        return [
            'nombre' => $nombre,
            'ruta' => $rutaZip,
            'bytes' => $bytes,
            'tablas' => count($tablas),
            'filas' => $filasTotal,
            'duracion_ms' => $duracionMs,
        ];
    }

    protected function volcarTabla($manejador, string $tabla): int
    {
        $columnas = $this->obtenerColumnas($tabla);
        $nombresColumnas = array_column($columnas, 'nombre');

        if ($nombresColumnas === []) {
            return 0;
        }

        $listaColumnas = implode(', ', array_map(fn ($c) => $this->identificador($c), $nombresColumnas));
        $prefijo = 'INSERT INTO ' . $this->identificador($tabla) . " ({$listaColumnas}) VALUES\n";
        $porLote = max(1, (int) config('respaldos.filas_por_insert', 200));

        $contador = 0;
        $buffer = [];

        $volcarLote = function () use ($manejador, &$buffer, $prefijo) {
            if ($buffer === []) {
                return;
            }
            fwrite($manejador, $prefijo . implode(",\n", $buffer) . ";\n");
            $buffer = [];
        };

        $acumularFila = function ($fila) use (&$buffer, &$contador, $volcarLote, $nombresColumnas, $porLote) {
            $valores = [];
            foreach ($nombresColumnas as $columna) {
                $valores[] = $this->valorSql($fila->{$columna} ?? null);
            }
            $buffer[] = '(' . implode(', ', $valores) . ')';
            $contador++;

            if (count($buffer) >= $porLote) {
                $volcarLote();
            }
        };

        if (in_array('id', $nombresColumnas, true)) {
            DB::table($tabla)->chunkById($porLote, function ($filas) use ($acumularFila) {
                foreach ($filas as $fila) {
                    $acumularFila($fila);
                }
            });
        } else {
            foreach (DB::table($tabla)->get() as $fila) {
                $acumularFila($fila);
            }
        }

        $volcarLote();

        return $contador;
    }

    protected function parcharTotalFilas(string $rutaSql, int $filasTotal): void
    {
        $marca = '-- FILAS: 000000000000';
        $reemplazo = sprintf('-- FILAS: %012d', $filasTotal);

        if (strlen($marca) !== strlen($reemplazo)) {
            return;
        }

        $manejador = fopen($rutaSql, 'r+');
        if ($manejador === false) {
            return;
        }

        try {
            $inicio = fread($manejador, 2048) ?: '';
            $posicion = strpos($inicio, $marca);

            if ($posicion !== false) {
                fseek($manejador, $posicion);
                fwrite($manejador, $reemplazo);
            }
        } finally {
            fclose($manejador);
        }
    }

    protected function cabeceraSql(?string $motivo, int $totalTablas): string
    {
        $config = config('database.connections.' . DB::connection()->getName(), []);
        $usuario = Auth::check()
            ? (($auth = Auth::user())->nombre_completo ?? $auth->name ?? $auth->email ?? 'usuario')
            : 'CLI (artisan)';

        $lineas = [
            '-- ' . self::MARCA . ' v' . self::VERSION_FORMATO,
            '-- FECHA: ' . now()->toIso8601String(),
            '-- MOTOR: ' . $this->driver,
            '-- HOST: ' . ($config['host'] ?? 'local'),
            '-- PUERTO: ' . ($config['port'] ?? '-'),
            '-- BASE: ' . ($config['database'] ?? '-'),
            '-- GENERADO_POR: ' . $usuario,
            '-- MOTIVO: ' . ($motivo ?: 'manual'),
            '-- TABLAS: ' . $totalTablas,
            '-- FILAS: 000000000000',
            '-- ADVERTENCIA: restaurar este archivo BORRA los datos actuales y los sustituye por el estado de este respaldo.',
            '',
        ];

        return implode("\n", $lineas);
    }

    public function listar(): array
    {
        $respaldos = [];

        foreach (glob($this->directorio() . DIRECTORY_SEPARATOR . 'respaldo_*.zip') ?: [] as $ruta) {
            try {
                $info = $this->leerCabecera(basename($ruta));
            } catch (Throwable) {
                $info = null;
            }

            $respaldos[] = [
                'nombre' => basename($ruta),
                'bytes' => filesize($ruta),
                'creado' => date('Y-m-d H:i:s', filemtime($ruta)),
                'motor' => $info['motor'] ?? '?',
                'base' => $info['base'] ?? '?',
                'motivo' => $info['motivo'] ?? '?',
                'generado_por' => $info['generado_por'] ?? '?',
                'tablas' => isset($info['tablas']) ? (int) $info['tablas'] : null,
                'filas' => isset($info['filas']) ? (int) $info['filas'] : null,
            ];
        }

        usort($respaldos, fn ($a, $b) => strcmp($b['nombre'], $a['nombre']));

        return $respaldos;
    }

    public function leerCabecera(string $nombre): array
    {
        $ruta = $this->rutaValida($nombre);
        $zip = new ZipArchive;

        if ($zip->open($ruta) !== true) {
            throw new \RuntimeException("El archivo de respaldo está dañado o no es un ZIP válido: {$nombre}");
        }

        $contenido = $zip->getFromIndex(0, 8192) ?: '';
        $zip->close();

        if (!str_contains($contenido, self::MARCA)) {
            throw new \RuntimeException("El archivo {$nombre} no es un respaldo del SGA.");
        }

        $metadatos = [];
        foreach (explode("\n", $contenido) as $linea) {
            if (!str_starts_with($linea, '-- ')) {
                break;
            }
            if (preg_match('/^-- ([A-Z_]+): (.*)$/', $linea, $m)) {
                $metadatos[strtolower($m[1])] = trim($m[2]);
            }
        }

        return $metadatos;
    }

    public function eliminar(string $nombre): bool
    {
        return @unlink($this->rutaValida($nombre));
    }

    public function rutaValida(string $nombre): string
    {
        $nombre = basename($nombre);

        if (!preg_match(self::PATRON_NOMBRE, $nombre)) {
            abort(422, 'Nombre de respaldo inválido.');
        }

        $ruta = $this->directorio() . DIRECTORY_SEPARATOR . $nombre;

        if (!is_file($ruta)) {
            abort(404, 'El respaldo indicado no existe.');
        }

        return $ruta;
    }

    protected function extraerSql(string $nombre): string
    {
        $ruta = $this->rutaValida($nombre);
        $zip = new ZipArchive;

        if ($zip->open($ruta) !== true) {
            throw new \RuntimeException("No fue posible abrir el respaldo: {$nombre}");
        }

        $contenido = $zip->getFromIndex(0);
        $zip->close();

        if ($contenido === false) {
            throw new \RuntimeException("El respaldo está dañado: {$nombre}");
        }

        return $contenido;
    }

    public function restaurar(string $nombre): array
    {
        $sql = $this->extraerSql($nombre);
        $meta = $this->leerCabecera($nombre);

        $this->pdo = DB::connection()->getPdo();
        $this->driver = DB::connection()->getDriverName();

        if (($meta['motor'] ?? null) !== $this->driver) {
            throw new \RuntimeException(sprintf(
                'El respaldo fue generado con el motor "%s" pero la conexión activa es "%s". La restauración fue cancelada por seguridad.',
                $meta['motor'] ?? 'desconocido',
                $this->driver
            ));
        }

        if (!str_contains($sql, 'BEGIN;') || !str_contains($sql, 'COMMIT;')) {
            throw new \RuntimeException('El respaldo no contiene una transacción completa; se cancela la restauración.');
        }

        $respaldoPrevio = $this->crearRespaldo('previo-a-restauracion');

        Log::warning('INICIANDO restauración de base de datos', ['archivo' => $nombre, 'previo' => $respaldoPrevio['nombre']]);

        try {
            DB::unprepared($sql);
        } catch (Throwable $e) {
            Log::error('La restauración FALLÓ (la transacción se revirtió)', ['archivo' => $nombre, 'error' => $e->getMessage()]);

            throw new \RuntimeException(
                'La restauración falló y NO se aplicó ningún cambio (la transacción se revirtió por completo). '
                . 'Los datos anteriores siguen intactos. Detalle: ' . $e->getMessage(),
                0,
                $e
            );
        }

        Log::warning('Restauración de base de datos COMPLETADA', ['archivo' => $nombre]);

        return [
            'archivo' => $nombre,
            'respaldo_previo' => $respaldoPrevio['nombre'],
            'filas' => (int) ($meta['filas'] ?? 0),
            'motor' => $this->driver,
        ];
    }

    public function estadoConexion(): array
    {
        $this->driver = DB::connection()->getDriverName();
        $nombreConexion = DB::connection()->getName();
        $config = config("database.connections.{$nombreConexion}", []);

        return [
            'driver' => $this->driver,
            'host' => $config['host'] ?? 'local',
            'puerto' => $config['port'] ?? '-',
            'base' => $config['database'] ?? '-',
            'tablas' => count($this->obtenerTablas()),
            'es_supabase' => str_contains((string) ($config['host'] ?? ''), 'supabase'),
        ];
    }

    protected function obtenerTablas(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return array_map('current', DB::select(
                "SELECT table_name FROM information_schema.tables
                 WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
                 ORDER BY table_name"
            ));
        }

        if ($driver === 'mysql') {
            $dbName = DB::connection()->getDatabaseName();
            $filas = DB::select(
                "SELECT table_name FROM information_schema.tables
                 WHERE table_schema = ? AND table_type = 'BASE TABLE'
                 ORDER BY table_name",
                [$dbName]
            );

            return array_map(fn ($f) => (string) ($f->table_name ?? $f->TABLE_NAME ?? ''), $filas);
        }

        if ($driver === 'sqlite') {
            return array_map('current', DB::select(
                "SELECT name FROM sqlite_master
                 WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
                 ORDER BY name"
            ));
        }

        throw new \RuntimeException("Motor de base de datos no soportado por el módulo de respaldos: {$driver}");
    }

    protected function obtenerDependencias(): array
    {
        $driver = DB::connection()->getDriverName();
        $dependencias = [];

        foreach ($this->obtenerTablas() as $tabla) {
            $dependencias[$tabla] = [];
        }

        if ($driver === 'pgsql') {
            $filas = DB::select(
                "SELECT DISTINCT tc.table_name AS tabla, ccu.table_name AS referencia
                 FROM information_schema.table_constraints tc
                 JOIN information_schema.constraint_column_usage ccu
                   ON ccu.constraint_name = tc.constraint_name
                  AND ccu.table_schema = tc.table_schema
                 WHERE tc.constraint_type = 'FOREIGN KEY'
                   AND tc.table_schema = 'public'
                   AND ccu.table_name IS NOT NULL"
            );

            foreach ($filas as $fila) {
                if ($fila->tabla !== $fila->referencia) {
                    $dependencias[$fila->tabla][] = $fila->referencia;
                }
            }

            return $dependencias;
        }

        if ($driver === 'mysql') {
            $dbName = DB::connection()->getDatabaseName();
            $filas = DB::select(
                "SELECT DISTINCT table_name AS tabla, referenced_table_name AS referencia
                 FROM information_schema.key_column_usage
                 WHERE table_schema = ? AND referenced_table_name IS NOT NULL",
                [$dbName]
            );

            foreach ($filas as $fila) {
                $tabla = (string) ($fila->tabla ?? $fila->TABLA ?? '');
                $ref = (string) ($fila->referencia ?? $fila->REFERENCIA ?? '');
                if ($tabla !== '' && $ref !== '' && $tabla !== $ref && isset($dependencias[$tabla])) {
                    $dependencias[$tabla][] = $ref;
                }
            }

            return $dependencias;
        }

        foreach (array_keys($dependencias) as $tabla) {
            $foraneas = DB::select("PRAGMA foreign_key_list({$this->identificador($tabla, false)})");
            foreach ($foraneas as $foranea) {
                if ($foranea->table && $foranea->table !== $tabla && isset($dependencias[$foranea->table])) {
                    $dependencias[$tabla][] = $foranea->table;
                }
            }
        }

        return $dependencias;
    }

    protected function obtenerTablasOrdenadas(): array
    {
        $dependencias = $this->obtenerDependencias();
        $pendientes = array_keys($dependencias);
        $ordenadas = [];

        while ($pendientes !== []) {
            $listas = array_values(array_filter($pendientes, function ($tabla) use ($dependencias, $ordenadas) {
                return empty(array_diff(array_unique($dependencias[$tabla]), $ordenadas));
            }));

            if ($listas === []) {
                sort($pendientes);

                return array_merge($ordenadas, $pendientes);
            }

            sort($listas);
            foreach ($listas as $tabla) {
                $ordenadas[] = $tabla;
            }

            $pendientes = array_values(array_diff($pendientes, $listas));
        }

        return $ordenadas;
    }

    protected function obtenerColumnas(string $tabla): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $filas = DB::select(
                'SELECT column_name, column_default FROM information_schema.columns
                 WHERE table_schema = ? AND table_name = ? ORDER BY ordinal_position',
                ['public', $tabla]
            );

            return array_map(fn ($f) => [
                'nombre' => $f->column_name,
                'default' => $f->column_default,
            ], $filas);
        }

        if ($driver === 'mysql') {
            $dbName = DB::connection()->getDatabaseName();
            $filas = DB::select(
                'SELECT column_name, column_default FROM information_schema.columns
                 WHERE table_schema = ? AND table_name = ? ORDER BY ordinal_position',
                [$dbName, $tabla]
            );

            return array_map(fn ($f) => [
                'nombre' => (string) ($f->column_name ?? $f->COLUMN_NAME ?? ''),
                'default' => $f->column_default ?? $f->COLUMN_DEFAULT ?? null,
            ], $filas);
        }

        $filas = DB::select("PRAGMA table_info({$this->identificador($tabla, false)})");

        return array_map(fn ($f) => [
            'nombre' => $f->name,
            'default' => $f->dflt_value,
        ], $filas);
    }

    protected function columnasSerial(string $tabla): array
    {
        return array_map(
            fn ($c) => $c['nombre'],
            array_filter($this->obtenerColumnas($tabla), fn ($c) => str_contains((string) $c['default'], 'nextval'))
        );
    }

    protected function identificador(string $nombre, bool $entreComillas = true): string
    {
        if ($this->driver === 'mysql') {
            $limpio = str_replace('`', '``', $nombre);

            return $entreComillas ? "`{$limpio}`" : $limpio;
        }

        $limpio = str_replace('"', '""', $nombre);

        return $entreComillas ? "\"{$limpio}\"" : $limpio;
    }

    protected function valorSql(mixed $valor): string
    {
        if ($valor === null) {
            return 'NULL';
        }

        if (is_bool($valor)) {
            return $this->pdo->quote($valor ? '1' : '0');
        }

        if (is_int($valor) || is_float($valor)) {
            return (string) $valor;
        }

        if (is_resource($valor)) {
            $valor = stream_get_contents($valor) ?: '';
        }

        return $this->pdo->quote((string) $valor);
    }

    protected function aplicarRetencion(): void
    {
        $maximo = max(1, (int) config('respaldos.retencion', 20));
        $archivos = glob($this->directorio() . DIRECTORY_SEPARATOR . 'respaldo_*.zip') ?: [];

        if (count($archivos) <= $maximo) {
            return;
        }

        usort($archivos, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($archivos, $maximo) as $antiguo) {
            @unlink($antiguo);
            Log::info('Respaldo eliminado por política de retención', ['archivo' => basename($antiguo)]);
        }
    }

    public function directorio(): string
    {
        $directorio = config('respaldos.directorio', storage_path('app/respaldos'));

        if (!is_dir($directorio)) {
            @mkdir($directorio, 0775, true);
        }

        return $directorio;
    }
}