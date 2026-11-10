<?php

namespace Tests\Feature\Authorization;

use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Aula;
use App\Models\Grado;
use App\Models\Modalidad;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\AulaAsignaturaDocente;
use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Horario;
use App\Models\BloqueHorario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->modalidad = Modalidad::factory()->create(['nombre' => 'Primaria']);
        $this->grado = Grado::factory()->create(['modalidad_id' => $this->modalidad->id, 'nombre' => '1ro']);
        $this->anioEscolar = AnioEscolar::factory()->create(['estado' => 'activo', 'nombre' => '2026']);
        $this->aula = Aula::factory()->create([
            'grado_id' => $this->grado->id,
            'modalidad_id' => $this->modalidad->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'nombre' => 'A',
            'turno' => 'Mañana',
        ]);
        $this->asignatura = Asignatura::factory()->create(['nombre' => 'Matemáticas']);
        $this->bloque = BloqueHorario::factory()->create([
            'modalidad_id' => $this->modalidad->id,
            'turno' => 'Mañana',
            'hora_inicio' => '07:00',
            'hora_fin' => '07:45',
        ]);
    }

    /** @test */
    public function director_puede_ver_cualquier_horario()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $horario = Horario::factory()->create();
        
        $this->actingAs($director)
            ->assertCan('viewAny', Horario::class)
            ->assertCan('view', $horario);
    }

    /** @test */
    public function docente_asignatura_puede_ver_horario_de_su_asignatura()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente por Asignatura');
        $docenteUser->givePermissionTo(['horarios.ver', 'horarios.gestionar']);
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $this->asignatura->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $horario = Horario::factory()->create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'bloque_horario_id' => $this->bloque->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('view', $horario);
    }

    /** @test */
    public function docente_guia_puede_ver_horario_de_su_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo(['horarios.ver', 'horarios.gestionar']);
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $this->asignatura->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $horario = Horario::factory()->create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'bloque_horario_id' => $this->bloque->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('view', $horario);
    }

    /** @test */
    public function docente_asignatura_NO_puede_ver_horario_de_otra_asignatura()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente por Asignatura');
        $docenteUser->givePermissionTo(['horarios.ver', 'horarios.gestionar']);
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $this->asignatura->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $otraAsignatura = Asignatura::factory()->create(['nombre' => 'Lengua']);
        $otraAsignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $otraAsignatura->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $horario = Horario::factory()->create([
            'aula_asignatura_docente_id' => $otraAsignacion->id,
            'bloque_horario_id' => $this->bloque->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCant('view', $horario);
    }

    /** @test */
    public function docente_puede_crear_horario_para_su_asignatura()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente por Asignatura');
        $docenteUser->givePermissionTo('horarios.gestionar');
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $this->asignatura->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('create', Horario::class);
    }

    /** @test */
    public function docente_puede_actualizar_horario_de_su_asignatura()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente por Asignatura');
        $docenteUser->givePermissionTo('horarios.gestionar');
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $this->asignatura->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $horario = Horario::factory()->create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'bloque_horario_id' => $this->bloque->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('update', $horario);
    }

    /** @test */
    public function solo_director_subdirector_pueden_eliminar_horario()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente por Asignatura');
        $docenteUser->givePermissionTo('horarios.gestionar');
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $horario = Horario::factory()->create([
            'aula_asignatura_docente_id' => $asignacion->id,
        ]);
        
        $this->actingAs($director)
            ->assertCan('delete', $horario);
        
        $this->actingAs($docenteUser)
            ->assertCant('delete', $horario);
    }
}