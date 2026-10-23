<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use ZipArchive;

/**
 * Motor de respaldo y restauración de la base de datos.
 *
 * Generado tras el incidente de Oct 2026 en el que se perdió el contenido
 * de la base de datos de Supabase (ver Contexto.md, secciones 15.6 y 16).
 *
 * Diseño:
 * - 100% PHP: no depende de binarios externos (pg_dump/psql no existen en
 *   el servidor Laragon local) y funciona contra el pooler de Supabase.
 * - El volcado es un archivo SQL con DELETEs + INSERTs ordenados según las
 *   dependencias de llaves foráneas (padres primero), envuelto en una
 *   transacción BEGIN ... COMMIT. La restauración se ejecuta como una única
 *   consulta multi-statement, por lo que es atómica: todo o nada.
 * - Incluye TODAS las tablas del esquema (migraciones, sesiones, caché,
 *   permisos de Spatie, etc.) para reproducir el estado exacto.
 * - Compatible con pgsql (Supabase) y sqlite (pruebas automatizadas).
 *
 * Regla del incidente (15.6): este servicio NUNCA debe ejecutarse en un
 * entorno de pruebas apuntando a la base real, ni viceversa. El método
 * restaurar() valida que el motor del archivo coincida con la conexión
 * activa y siempre crea un respaldo de seguridad previo.
 */
class RespaldoService
{
    /** Marca de cabecera para validar archivos propios. */
    public const MARCA = 'SGA-RESPALDO';

    /** Versión del formato de volcado. */
    public const VERSION_FORMATO = 1;

    /** Patrón estricto de nombre de archivo de respaldo (evita path traversal). */
    public const PATRON_NOMBRE = '/^respaldo_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{6}(_[0-9]+)?\.zip$/';

    /** @var \PDO Conexión PDO activa (para quoting fiel según el motor). */
    protected $pdo;

    /** @var string Driver activo: 'pgsql' o 'sqlite'. */
    protected string $driver;

    // =========================================================================
    // CREACIÓN DE RESPALDOS
    // =========================================================================

    /**
     * Genera un respaldo completo y lo comprime en .zip dentro del
     * directorio configurado. Aplica la política de retención.
     *
     * @return array{nombre: string, ruta: string, bytes: int, tablas: int, filas: int, duracion_ms: int}
     *
     * @throws Throwable
     */
    public function crearRespaldo(?string $motivo = null): array
    {
        $inicio = microtime(true);

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $this->pdo = DB::connection()->getPdo();
        $this->driver = DB::connection()->getDriverName();

        $tablas = $this->obtenerTablasOrdenadas(); // padres primero
        $excluir = config('respaldos.excluir_tablas', []);
        $tablas = array_values(array_diff($tablas, $excluir));

        // Nombre único: si en el mismo segundo ya existe un respaldo (p. ej.
        // el pre-respaldo automático de una restauración), se numera.
        $nombre = 'respaldo_'.now()->format('Y-m-d_His').'.zip';
        $base = substr($nombre, 0, -4);
        $secuencia = 2;
        while (file_exists($this->directorio().DIRECTORY_SEPARATOR.$nombre)) {
            $nombre = "{$base}_{$secuencia}.zip";
            $secuencia++;
        }

        $rutaZip = $this->directorio().DIRECTORY_SEPARATOR.$nombre;
        $rutaSql = substr($rutaZip, 0, -4).'.sql';

        $filasTotal = 0;
        $manejador = fopen($rutaSql, 'w');

        try {
            // La marca FILAS usa ancho fijo (12 dígitos) para poder parchar el
            // total exacto en el mismo archivo una vez terminado el volcado.
            fwrite($manejador, $this->cabeceraSql($motivo, count($tablas)));

            // 1) Apertura de la transacción única de restauración.
            fwrite($manejador, "BEGIN;\n\n");

            // 2) DELETEs en orden INVERSO (hijos antes que padres) para que
            //    la restauración sea repetible sin violar llaves foráneas.
            fwrite($manejador, "-- ===== LIMPIEZA (orden inverso de dependencias) =====\n");
            foreach (array_reverse($tablas) as $tabla) {
                fwrite($manejador, 'DELETE FROM '.$this->identificador($tabla).";\n");
            }

            // 3) INSERTs en orden directo (padres antes que hijos).
            fwrite($manejador, "\n-- ===== DATOS (orden de dependencias) =====\n");
            foreach ($tablas as $tabla) {
                $filasTotal += $this->volcarTabla($manejador, $tabla);
            }

            // 4) Sincronizar secuencias (solo PostgreSQL).
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

            // 5) Cierre de la transacción.
            fwrite($manejador, "\nCOMMIT;\n");
            fwrite($manejador, sprintf("\n-- FIN DEL RESPALDO — %s filas — generado por el SGA.\n", number_format($filasTotal, 0, '.', ',')));
        } finally {
            fclose($manejador);
        }

        $this->parcharTotalFilas($rutaSql, $filasTotal);

        // Comprimir y descartar el SQL temporal.
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

    /**
     * Vuelca una tabla completa en sentencias INSERT multi-row.
     * Lee por lotes usando la columna id cuando existe para no cargar
     * tablas grandes completas en memoria.
     */
    protected function volcarTabla($manejador, string $tabla): int
    {
        $columnas = $this->obtenerColumnas($tabla);
        $nombresColumnas = array_column($columnas, 'nombre');

        if (empty($nombresColumnas)) {
            return 0;
        }

        $listaColumnas = implode(', ', array_map(fn ($c) => $this->identificador($c), $nombresColumnas));
        $prefijo = 'INSERT INTO '.$this->identificador($tabla)." ({$listaColumnas}) VALUES\n";
        $porLote = max(1, (int) config('respaldos.filas_por_insert', 200));

        $contador = 0;
        $buffer = [];

        // Escribe el lote acumulado como una sentencia INSERT multi-row.
        $volcarLote = function () use ($manejador, &$buffer, $prefijo) {
            if (empty($buffer)) {
                return;
            }
            fwrite($manejador, $prefijo.implode(",\n", $buffer).";\n");
            $buffer = [];
        };

        // Acumula una fila en el lote actual.
        $acumularFila = function ($fila) use (&$buffer, &$contador, $volcarLote, $nombresColumnas, $porLote) {
            $valores = [];
            foreach ($nombresColumnas as $columna) {
                $valores[] = $this->valorSql($fila->{$columna} ?? null);
            }
            $buffer[] = '('.implode(', ', $valores).')';
            $contador++;

            if (count($buffer) >= $porLote) {
                $volcarLote();
            }
        };

        if (in_array('id', $nombresColumnas, true)) {
            // chunkById se encarga del orden por id y de avanzar por ventana.
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

        // Vaciar el último lote incompleto.
        $volcarLote();

        return $contador;
    }

    /**
     * Sustituye la marca de filas de la cabecera por el total real,
     * reescribiendo en sitio (mismo ancho: 12 dígitos).
     */
    protected function parcharTotalFilas(string $rutaSql, int $filasTotal): void
    {
        $marca = '-- FILAS: 000000000000';
        $reemplazo = sprintf('-- FILAS: %012d', $filasTotal);

        if (strlen($marca) !== strlen($reemplazo)) {
            return; // nunca debería ocurrir
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

    /** Cabecera del archivo SQL con metadatos legibles por leerCabecera(). */
    protected function cabeceraSql(?string $motivo, int $totalTablas): string
    {
        $config = config('database.connections.'.DB::connection()->getName(), []);
        $usuario = auth()->check()
            ? (($auth = auth()->user())->nombre_completo ?? $auth->name ?? $auth->email ?? 'usuario')
            : 'CLI (artisan)';

        $lineas = [
            '-- '.self::MARCA.' v'.self::VERSION_FORMATO,
            '-- FECHA: '.now()->toIso8601String(),
            '-- MOTOR: '.$this->driver,
            '-- HOST: '.($config['host'] ?? 'local'),
            '-- PUERTO: '.($config['port'] ?? '-'),
            '-- BASE: '.($config['database'] ?? '-'),
            '-- GENERADO_POR: '.$usuario,
            '-- MOTIVO: '.($motivo ?: 'manual'),
            '-- TABLAS: '.$totalTablas,
            '-- FILAS: 000000000000',
            '-- ADVERTENCIA: restaurar este archivo BORRA los datos actuales y los sustituye por el estado de este respaldo.',
            '',
        ];

        return implode("\n", $lineas);
    }

    // =========================================================================
    // LISTADO, LECTURA Y ELIMINACIÓN
    // =========================================================================

    /** Lista los respaldos disponibles, el más reciente primero. */
    public function listar(): array
    {
        $respaldos = [];

        foreach (glob($this->directorio().DIRECTORY_SEPARATOR.'respaldo_*.zip') ?: [] as $ruta) {
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
                'tablas' => $info['tablas'] !== null ? (int) $info['tablas'] : null,
                'filas' => $info['filas'] !== null ? (int) $info['filas'] : null,
            ];
        }

        usort($respaldos, fn ($a, $b) => strcmp($b['nombre'], $a['nombre']));

        return $respaldos;
    }

    /** Lee la cabecera de metadatos de un respaldo sin extraerlo completo. */
    public function leerCabecera(string $nombre): array
    {
        $ruta = $this->rutaValida($nombre);
        $zip = new ZipArchive;

        if ($zip->open($ruta) !== true) {
            throw new \RuntimeException("El archivo de respaldo está dañado o no es un ZIP válido: {$nombre}");
        }

        // Solo se leen los primeros 8 KB: la cabecera de metadatos vive ahí.
        $contenido = $zip->getFromIndex(0, 8192) ?: '';
        $zip->close();

        if (! str_contains($contenido, self::MARCA)) {
            throw new \RuntimeException("El archivo {$nombre} no es un respaldo del SGA.");
        }

        $metadatos = [];
        foreach (explode("\n", $contenido) as $linea) {
            if (! str_starts_with($linea, '-- ')) {
                break; // terminó la cabecera
            }
            if (preg_match('/^-- ([A-Z_]+): (.*)$/', $linea, $m)) {
                $metadatos[strtolower($m[1])] = trim($m[2]);
            }
        }

        return $metadatos;
    }

    /** Elimina un respaldo por nombre. */
    public function eliminar(string $nombre): bool
    {
        return @unlink($this->rutaValida($nombre));
    }

    /** Ruta absoluta validada contra el patrón de nombres y el directorio. */
    public function rutaValida(string $nombre): string
    {
        $nombre = basename($nombre);

        if (! preg_match(self::PATRON_NOMBRE, $nombre)) {
            abort(422, 'Nombre de respaldo inválido.');
        }

        $ruta = $this->directorio().DIRECTORY_SEPARATOR.$nombre;

        if (! is_file($ruta)) {
            abort(404, 'El respaldo indicado no existe.');
        }

        return $ruta;
    }

    /** Extrae el contenido SQL de un respaldo a una ruta temporal. */
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

    // =========================================================================
    // RESTAURACIÓN
    // =========================================================================

    /**
     * Restaura un respaldo sobre la conexión ACTIVA.
     *
     * Salvaguardas (lección del incidente 15.6):
     *  1. El motor del archivo debe coincidir con la conexión activa.
     *  2. Crea un respaldo de seguridad ANTES de restaurar (hace el proceso
     *     reversible salvo desastre total del disco).
     *  3. Ejecuta el SQL completo en una sola consulta multi-statement con
     *     BEGIN/COMMIT embebidos: todo o nada.
     *
     * La confirmación humana ("RESTAURAR" escrito) es responsabilidad del
     * controlador / comando que invoca este método.
     *
     * @return array{archivo: string, respaldo_previo: string, filas: int, motor: string}
     */
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

        if (! str_contains($sql, 'BEGIN;') || ! str_contains($sql, 'COMMIT;')) {
            throw new \RuntimeException('El respaldo no contiene una transacción completa; se cancela la restauración.');
        }

        // 1) Respaldo de seguridad del estado actual (hace reversible el restore).
        $respaldoPrevio = $this->crearRespaldo('previo-a-restauracion');

        // 2) Restauración atómica: una sola consulta con BEGIN ... COMMIT.
        Log::warning('INICIANDO restauración de base de datos', ['archivo' => $nombre, 'previo' => $respaldoPrevio['nombre']]);

        try {
            DB::unprepared($sql);
        } catch (Throwable $e) {
            Log::error('La restauración FALLÓ (la transacción se revirtió)', ['archivo' => $nombre, 'error' => $e->getMessage()]);

            throw new \RuntimeException(
                'La restauración falló y NO se aplicó ningún cambio (la transacción se revirtió por completo). '.
                'Los datos anteriores siguen intactos. Detalle: '.$e->getMessage(),
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

    // =========================================================================
    // ESTADO DE LA CONEXIÓN (transparencia del destino, regla 15.6)
    // =========================================================================

    /** Devuelve el detalle de la conexión activa y su volumen de tablas. */
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

    // =========================================================================
    // INTROSPECCIÓN DEL ESQUEMA
    // =========================================================================

    /** Lista de tablas del esquema principal sin orden de dependencias. */
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

        if ($driver === 'sqlite') {
            return array_map('current', DB::select(
                "SELECT name FROM sqlite_master
                 WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
                 ORDER BY name"
            ));
        }

        throw new \RuntimeException("Motor de base de datos no soportado por el módulo de respaldos: {$driver}");
    }

    /** Mapa tabla => tablas padre (referenciadas por sus llaves foráneas). */
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

        // SQLite
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

    /**
     * Tablas ordenadas topológicamente por llaves foráneas (Kahn):
     * primero las que no dependen de nadie; desempate alfabético.
     * Si existiera un ciclo, las tablas restantes se agregan al final
     * en orden alfabético.
     */
    protected function obtenerTablasOrdenadas(): array
    {
        $dependencias = $this->obtenerDependencias();
        $pendientes = array_keys($dependencias);
        $ordenadas = [];

        while (! empty($pendientes)) {
            $listas = array_values(array_filter($pendientes, function ($tabla) use ($dependencias, $ordenadas) {
                return empty(array_diff(array_unique($dependencias[$tabla]), $ordenadas));
            }));

            if (empty($listas)) {
                // Ciclo de dependencias: volcar el resto alfabéticamente.
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

    /** Columnas de una tabla: nombre y default (para detectar seriales). */
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

        // SQLite
        $filas = DB::select("PRAGMA table_info({$this->identificador($tabla, false)})");

        return array_map(fn ($f) => [
            'nombre' => $f->name,
            'default' => $f->dflt_value,
        ], $filas);
    }

    /** Columnas autoincrementales (nextval) de una tabla. */
    protected function columnasSerial(string $tabla): array
    {
        return array_map(
            fn ($c) => $c['nombre'],
            array_filter($this->obtenerColumnas($tabla), fn ($c) => str_contains((string) $c['default'], 'nextval'))
        );
    }

    // =========================================================================
    // UTILIDADES DE FORMATEO SQL
    // =========================================================================

    /** Identificador SQL entre comillas dobles (compatible pgsql/sqlite). */
    protected function identificador(string $nombre, bool $entreComillas = true): string
    {
        $limpio = str_replace('"', '""', $nombre);

        return $entreComillas ? "\"{$limpio}\"" : $limpio;
    }

    /** Convierte un valor de PHP a su literal SQL fiel según el motor activo. */
    protected function valorSql(mixed $valor): string
    {
        if ($valor === null) {
            return 'NULL';
        }

        if (is_bool($valor)) {
            // '1'/'0' citado: PostgreSQL lo castea a boolean y SQLite lo acepta.
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

    // =========================================================================
    // RETENCIÓN Y DIRECTORIO
    // =========================================================================

    /** Elimina los respaldos más antiguos que excedan la retención configurada. */
    protected function aplicarRetencion(): void
    {
        $maximo = max(1, (int) config('respaldos.retencion', 20));
        $archivos = glob($this->directorio().DIRECTORY_SEPARATOR.'respaldo_*.zip') ?: [];

        if (count($archivos) <= $maximo) {
            return;
        }

        // Ordenar del más nuevo al más viejo y eliminar el excedente.
        usort($archivos, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($archivos, $maximo) as $antiguo) {
            @unlink($antiguo);
            Log::info('Respaldo eliminado por política de retención', ['archivo' => basename($antiguo)]);
        }
    }

    /** Directorio de respaldos (creado en caso de no existir). */
    public function directorio(): string
    {
        $directorio = config('respaldos.directorio', storage_path('app/respaldos'));

        if (! is_dir($directorio)) {
            @mkdir($directorio, 0775, true);
        }

        return $directorio;
    }
}
