<?php

namespace Tests\Feature\Authorization;

use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Aula;
use App\Models\Grado;
use App\Models\Modalidad;
use App\Models\AnioEscolar;
use App\Models\Matricula;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatriculaPolicyTest extends TestCase
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
    }

    /** @test */
    public function director_puede_ver_cualquier_matricula()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $matricula = Matricula::factory()->create([
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $this->actingAs($director)
            ->assertCan('viewAny', Matricula::class)
            ->assertCan('view', $matricula);
    }

    /** @test */
    public function docente_guia_puede_ver_matricula_de_su_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo(['alumnos.ver', 'alumnos.gestionar']);
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('view', $matricula);
    }

    /** @test */
    public function docente_guia_NO_puede_ver_matricula_de_otra_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo(['alumnos.ver', 'alumnos.gestionar']);
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $otraAula = Aula::factory()->create([
            'grado_id' => $this->grado->id,
            'modalidad_id' => $this->modalidad->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'nombre' => 'B',
        ]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $otraAula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCant('view', $matricula);
    }

    /** @test */
    public function coordinador_puede_ver_matricula_de_su_modalidad()
    {
        $coordinadorUser = Usuario::factory()->create();
        $coordinadorUser->assignRole('Coordinador');
        $coordinadorUser->givePermissionTo(['alumnos.ver', 'alumnos.gestionar']);
        
        $docente = Docente::factory()->create([
            'usuario_id' => $coordinadorUser->id,
            'es_coordinador' => true,
            'modalidad_coordina_id' => $this->modalidad->id,
        ]);
        
        $this->aula->update(['modalidad_id' => $this->modalidad->id]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $this->actingAs($coordinadorUser)
            ->assertCan('view', $matricula);
    }

    /** @test */
    public function coordinador_NO_puede_ver_matricula_de_otra_modalidad()
    {
        $coordinadorUser = Usuario::factory()->create();
        $coordinadorUser->assignRole('Coordinador');
        $coordinadorUser->givePermissionTo(['alumnos.ver', 'alumnos.gestionar']);
        
        $docente = Docente::factory()->create([
            'usuario_id' => $coordinadorUser->id,
            'es_coordinador' => true,
            'modalidad_coordina_id' => $this->modalidad->id,
        ]);
        
        $otraModalidad = Modalidad::factory()->create(['nombre' => 'Secundaria']);
        $otraAula = Aula::factory()->create([
            'grado_id' => $this->grado->id,
            'modalidad_id' => $otraModalidad->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'nombre' => 'A',
        ]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $otraAula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $this->actingAs($coordinadorUser)
            ->assertCant('view', $matricula);
    }

    /** @test */
    public function alumno_puede_ver_su_propia_matricula()
    {
        $alumnoUser = Usuario::factory()->create();
        $alumnoUser->assignRole('Alumno');
        
        $alumno = Alumno::factory()->create(['usuario_id' => $alumnoUser->id]);
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $this->actingAs($alumnoUser)
            ->assertCan('view', $matricula);
    }

    /** @test */
    public function alumno_NO_puede_ver_matricula_de_otro_alumno()
    {
        $alumnoUser = Usuario::factory()->create();
        $alumnoUser->assignRole('Alumno');
        
        $alumno = Alumno::factory()->create(['usuario_id' => $alumnoUser->id]);
        
        $otroAlumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $otroAlumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $this->actingAs($alumnoUser)
            ->assertCant('view', $matricula);
    }

    /** @test */
    public function secretaria_puede_actualizar_matricula()
    {
        $secretaria = Usuario::factory()->create();
        $secretaria->assignRole('Secretaria');
        $secretaria->givePermissionTo('alumnos.gestionar');
        
        $matricula = Matricula::factory()->create([
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $this->actingAs($secretaria)
            ->assertCan('update', $matricula);
    }

    /** @test */
    public function docente_guia_puede_actualizar_matricula_de_su_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo('alumnos.gestionar');
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('update', $matricula);
    }

    /** @test */
    public function solo_director_subdirector_secretaria_gestor_pueden_eliminar_matricula()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo('alumnos.gestionar');
        
        $matricula = Matricula::factory()->create([
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $this->actingAs($director)
            ->assertCan('delete', $matricula);
        
        $this->actingAs($docenteUser)
            ->assertCant('delete', $matricula);
    }
}