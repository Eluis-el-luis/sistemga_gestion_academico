<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Asignatura;
use App\Models\AvanceContenido;
use App\Models\ApoyoPadres;
use App\Models\BloqueHorario;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\ReparacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase7Test extends TestCase
{
    use RefreshDatabase;

    protected Usuario $director;
    protected Usuario $docenteAsignatura;
    protected Aula $aula;
    protected AulaAsignaturaDocente $asignacion;
    protected Matricula $matricula;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\Seeders\PermisoSeeder']);

        $roles = ['Director', 'Subdirector', 'Coordinador', 'Docente Guia', 'Docente por Asignatura', 'Gestor de Usuarios', 'Alumno'];
        foreach ($roles as $nombre) {
            Rol::firstOrCreate(['nombre' => $nombre]);
        }

        $this->director = Usuario::factory()->create([
            'rol_id' => Rol::where('nombre', 'Director')->first()->id,
        ]);
        $this->director->assignRole('Director');

        $this->docenteAsignatura = Usuario::factory()->create([
            'rol_id' => Rol::where('nombre', 'Docente por Asignatura')->first()->id,
        ]);
        $this->docenteAsignatura->assignRole('Docente por Asignatura');

        $docenteModel = Docente::factory()->create(['usuario_id' => $this->docenteAsignatura->id]);

        $modalidad = Modalidad::factory()->create(['nombre' => 'Científico Humanista']);
        $grado = Grado::factory()->create(['nombre' => '1ro Secundaria', 'modalidad_id' => $modalidad->id]);
        $anio = AnioEscolar::factory()->create(['nombre' => '2026', 'activo' => true]);
        $asignatura = Asignatura::factory()->create(['nombre' => 'Matemática']);

        $this->aula = Aula::factory()->create([
            'modalidad_id' => $modalidad->id,
            'grado_id' => $grado->id,
            'anio_escolar_id' => $anio->id,
        ]);

        $this->asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $asignatura->id,
            'docente_id' => $docenteModel->id,
            'anio_escolar_id' => $anio->id,
        ]);

        $alumno = Alumno::factory()->create();
        $this->matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $anio->id,
            'estado' => 'activo',
        ]);
    }

    
    public function testdirector_puede_acceder_a_modulos_fase7(): void
    {
        $this->actingAs($this->director)
            ->get(route('academico.boletines.index'))
            ->assertOk();

        $this->actingAs($this->director)
            ->get(route('academico.reparacion.index'))
            ->assertOk();

        $this->actingAs($this->director)
            ->get(route('academico.avance.index'))
            ->assertOk();

        $this->actingAs($this->director)
            ->get(route('academico.apoyo-padres.index'))
            ->assertOk();
    }

    
    public function testdocente_asignatura_puede_registrar_avance(): void
    {
        $this->actingAs($this->docenteAsignatura)
            ->post(route('academico.avance.store'), [
                'aula_asignatura_docente_id' => $this->asignacion->id,
                'mes' => '2026-08',
                'porcentaje_avance' => 45,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('avance_contenido', [
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'mes' => '2026-08',
            'porcentaje_avance' => 45,
        ]);
    }

    
    public function testregla_de_reparacion_aprueba_desde_60(): void
    {
        $service = app(ReparacionService::class);

        $this->assertEquals('aprobado', $service->evaluarResultado(60));
        $this->assertEquals('aprobado', $service->evaluarResultado(75));
        $this->assertEquals('reprobado', $service->evaluarResultado(59));
        $this->assertEquals('reprobado', $service->evaluarResultado(0));
    }

    
    public function testreparacion_es_idempotente_por_matricula_asignatura(): void
    {
        $service = app(ReparacionService::class);

        $asignatura = Asignatura::first();

        $service->registrar($this->matricula, $asignatura, 55.0, '2026-08-01');
        $service->registrar($this->matricula, $asignatura, 65.0, '2026-08-02');

        $registros = \App\Models\ExamenReparacion::where('matricula_id', $this->matricula->id)
            ->where('asignatura_id', $asignatura->id)
            ->get();

        $this->assertCount(1, $registros);
        $this->assertEquals('aprobado', $registros->first()->resultado);
        $this->assertEquals(65.0, $registros->first()->nota_obtenida);
    }

    
    public function testapoyo_padres_valida_que_cantidad_no_supere_total(): void
    {
        $this->actingAs($this->director)
            ->post(route('academico.apoyo-padres.store'), [
                'aula_id' => $this->aula->id,
                'mes' => '2026-08',
                'cantidad_apoyan' => 40,
                'total_padres' => 30,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('apoyo_padres', [
            'aula_id' => $this->aula->id,
            'mes' => '2026-08',
        ]);
    }

    
    public function testapoyo_padres_es_idempotente(): void
    {
        $this->actingAs($this->director)
            ->post(route('academico.apoyo-padres.store'), [
                'aula_id' => $this->aula->id,
                'mes' => '2026-08',
                'cantidad_apoyan' => 20,
                'total_padres' => 30,
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->director)
            ->post(route('academico.apoyo-padres.store'), [
                'aula_id' => $this->aula->id,
                'mes' => '2026-08',
                'cantidad_apoyan' => 25,
                'total_padres' => 30,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('apoyo_padres', 1);
        $this->assertDatabaseHas('apoyo_padres', [
            'aula_id' => $this->aula->id,
            'mes' => '2026-08',
            'cantidad_apoyan' => 25,
        ]);
    }
}