<?php

namespace Tests\Feature;

use App\Services\RespaldoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Pruebas del motor de respaldos (RespaldoService).
 *
 * Corren 100% sobre SQLite :memory: (salvaguarda de TestCase.php): NUNCA
 * tocan la base de datos real de Supabase. El volcado de SQLite es el mismo
 * formato SQL del servicio, por lo que validan el ciclo completo
 * crear → destruir → restaurar sin riesgo.
 */
class RespaldoServiceTest extends TestCase
{
    protected RespaldoService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servicio = app(RespaldoService::class);

        // Directorio de respaldos aislado para pruebas.
        config(['respaldos.directorio' => storage_path('framework/testing/respaldos-test')]);

        // Estado inicial limpio.
        @mkdir(config('respaldos.directorio'), 0775, true);
        foreach (glob(config('respaldos.directorio').'/respaldo_*.zip') ?: [] as $archivo) {
            @unlink($archivo);
        }

        Schema::dropIfExists('prueba_alumnos');
        Schema::dropIfExists('prueba_grados');
        Schema::dropIfExists('prueba_pivot');

        // Tabla padre con serial.
        Schema::create('prueba_grados', function ($tabla) {
            $tabla->id();
            $tabla->string('nombre');
        });

        // Tabla hija con llave foránea (depende de prueba_grados).
        Schema::create('prueba_alumnos', function ($tabla) {
            $tabla->id();
            $tabla->unsignedBigInteger('grado_id');
            $tabla->string('nombre');
            $tabla->text('observaciones')->nullable();
            $tabla->boolean('activo')->default(true);
            $tabla->decimal('promedio', 5, 2)->nullable();
            $tabla->timestamps();

            $tabla->foreign('grado_id')->references('id')->on('prueba_grados');
        });

        // Tabla pivot SIN columna id (ruta alternativa de volcado).
        Schema::create('prueba_pivot', function ($tabla) {
            $tabla->unsignedBigInteger('grado_id');
            $tabla->string('etiqueta');
            $tabla->primary(['grado_id', 'etiqueta']);
        });

        // Datos con caracteres delicados: apóstrofes, comillas, saltos de
        // línea, tabulaciones, Unicode (ñ, emojis) y NULLs.
        DB::table('prueba_grados')->insert([
            ['nombre' => 'Primero "A"'],
            ['nombre' => "Segundo 'B' con ñ"],
        ]);

        DB::table('prueba_alumnos')->insert([
            [
                'grado_id' => 1,
                'nombre' => 'María José Ñúñez 👩‍🎓',
                'observaciones' => "Línea 1\nLínea 2\tcon tabulador y 'apóstrofe'",
                'activo' => true,
                'promedio' => 87.50,
            ],
            [
                'grado_id' => 2,
                'nombre' => 'Carlos O\'Brien',
                'observaciones' => null,
                'activo' => false,
                'promedio' => null,
            ],
        ]);

        DB::table('prueba_pivot')->insert([
            ['grado_id' => 1, 'etiqueta' => 'jornada-matutina'],
            ['grado_id' => 2, 'etiqueta' => 'jornada-vespertina'],
        ]);
    }

    protected function tearDown(): void
    {
        foreach (glob(config('respaldos.directorio').'/respaldo_*.zip') ?: [] as $archivo) {
            @unlink($archivo);
        }

        parent::tearDown();
    }

    public function test_crea_un_respaldo_con_zip_valido_y_metadatos(): void
    {
        $resultado = $this->servicio->crearRespaldo('prueba-automatica');

        $this->assertFileExists($resultado['ruta']);
        $this->assertSame(3, $resultado['tablas']);
        $this->assertSame(6, $resultado['filas']); // 2 grados + 2 alumnos + 2 pivots

        $lista = $this->servicio->listar();
        $this->assertCount(1, $lista);
        $this->assertSame($resultado['nombre'], $lista[0]['nombre']);
        $this->assertSame('sqlite', $lista[0]['motor']);
        $this->assertSame('prueba-automatica', $lista[0]['motivo']);
        $this->assertSame(6, $lista[0]['filas']);
    }

    public function test_restaura_datos_fieles_tras_destruccion_total(): void
    {
        $this->servicio->crearRespaldo('antes-del-desastre');

        // Simular el desastre del incidente: vaciar todas las tablas.
        DB::table('prueba_pivot')->delete();
        DB::table('prueba_alumnos')->delete();
        DB::table('prueba_grados')->delete();

        $this->assertSame(0, DB::table('prueba_alumnos')->count());

        $nombreRespaldo = $this->servicio->listar()[0]['nombre'];
        $resultado = $this->servicio->restaurar($nombreRespaldo);

        $this->assertSame(6, $resultado['filas']);
        $this->assertMatchesRegularExpression('/^respaldo_\d{4}-\d{2}-\d{2}_\d{6}(_\d+)?\.zip$/', $resultado['respaldo_previo']);

        // Los datos vuelven byte a byte, incluidos caracteres delicados.
        $this->assertSame(2, DB::table('prueba_grados')->count());
        $this->assertSame('Primero "A"', DB::table('prueba_grados')->where('id', 1)->value('nombre'));
        $this->assertSame("Segundo 'B' con ñ", DB::table('prueba_grados')->where('id', 2)->value('nombre'));

        $maria = DB::table('prueba_alumnos')->where('id', 1)->first();
        $this->assertSame('María José Ñúñez 👩‍🎓', $maria->nombre);
        $this->assertSame("Línea 1\nLínea 2\tcon tabulador y 'apóstrofe'", $maria->observaciones);
        $this->assertTrue((bool) $maria->activo);
        $this->assertEquals(87.5, (float) $maria->promedio);

        $carlos = DB::table('prueba_alumnos')->where('id', 2)->first();
        $this->assertSame("Carlos O'Brien", $carlos->nombre);
        $this->assertNull($carlos->observaciones);
        $this->assertFalse((bool) $carlos->activo);
        $this->assertNull($carlos->promedio);

        // La tabla sin columna id también se restaura.
        $this->assertSame(2, DB::table('prueba_pivot')->count());
        $this->assertSame('jornada-vespertina', DB::table('prueba_pivot')->where('grado_id', 2)->value('etiqueta'));

        // El respaldo de seguridad previo quedó registrado con su motivo.
        $listaFinal = $this->servicio->listar();
        $this->assertCount(2, $listaFinal);
        $this->assertSame('previo-a-restauracion', $listaFinal[0]['motivo']);
    }

    public function test_la_restauracion_es_repetible_por_los_delete_previos(): void
    {
        $this->servicio->crearRespaldo('repetible');

        $nombre = $this->servicio->listar()[0]['nombre'];

        // Restaurar dos veces seguidas sobre la misma base no debe violar
        // llaves foráneas ni duplicar datos.
        $this->servicio->restaurar($nombre);
        $this->servicio->restaurar($nombre);

        $this->assertSame(2, DB::table('prueba_grados')->count());
        $this->assertSame(2, DB::table('prueba_alumnos')->count());
    }

    public function test_aplica_la_politica_de_retencion(): void
    {
        config(['respaldos.retencion' => 2]);

        $this->servicio->crearRespaldo('uno');
        sleep(1);
        $this->servicio->crearRespaldo('dos');
        sleep(1);
        $this->servicio->crearRespaldo('tres');

        $lista = $this->servicio->listar();

        $this->assertCount(2, $lista);
        $this->assertSame('tres', $lista[0]['motivo']);
        $this->assertSame('dos', $lista[1]['motivo']);
    }

    public function test_rechaza_nombres_invalidos_por_seguridad(): void
    {
        $this->expectException(HttpException::class);

        $this->servicio->rutaValida('../.env');
    }

    public function test_rechaza_restaurar_un_respaldo_de_otro_motor(): void
    {
        $this->servicio->crearRespaldo('motor-correcto');

        $nombre = $this->servicio->listar()[0]['nombre'];

        // Falsificar la cabecera del respaldo para simular un dump de pgsql.
        $ruta = config('respaldos.directorio').'/'.$nombre;
        $zip = new \ZipArchive;
        $zip->open($ruta);
        $sql = $zip->getFromIndex(0);
        $zip->deleteIndex(0);
        $zip->addFromString('falso.sql', str_replace('-- MOTOR: sqlite', '-- MOTOR: pgsql', $sql));
        $zip->close();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cancelada por seguridad');

        $this->servicio->restaurar($nombre);
    }
}
