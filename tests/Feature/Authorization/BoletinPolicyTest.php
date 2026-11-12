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
use App\Models\Boletin;
use App\Models\CorteEvaluativo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoletinPolicyTest extends TestCase
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
        
        $this->corte = CorteEvaluativo::factory()->create([
            'anio_escolar_id' => $this->anioEscolar->id,
            'numero' => 1,
        ]);
    }

    /** @test */
    public function director_puede_ver_cualquier_boletin()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $boletin = Boletin::factory()->create();
        
        $this->actingAs($director)
            ->assertCan('viewAny', Boletin::class)
            ->assertCan('view', $boletin);
    }

    /** @test */
    public function docente_guia_puede_ver_boletin_de_su_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo(['boletines.ver', 'boletines.gestionar']);
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $boletin = Boletin::factory()->create([
            'matricula_id' => $matricula->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('view', $boletin);
    }

    /** @test */
    public function docente_guia_NO_puede_ver_boletin_de_otra_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo(['boletines.ver', 'boletines.gestionar']);
        
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
        
        $boletin = Boletin::factory()->create([
            'matricula_id' => $matricula->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCant('view', $boletin);
    }

    /** @test */
    public function coordinador_puede_ver_boletin_de_su_modalidad()
    {
        $coordinadorUser = Usuario::factory()->create();
        $coordinadorUser->assignRole('Coordinador');
        $coordinadorUser->givePermissionTo(['boletines.ver', 'boletines.gestionar']);
        
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
        
        $boletin = Boletin::factory()->create([
            'matricula_id' => $matricula->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($coordinadorUser)
            ->assertCan('view', $boletin);
    }

    /** @test */
    public function docente_guia_puede_actualizar_boletin_de_su_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo('boletines.gestionar');
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $boletin = Boletin::factory()->create([
            'matricula_id' => $matricula->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('update', $boletin);
    }

    /** @test */
    public function secretaria_puede_actualizar_boletin()
    {
        $secretaria = Usuario::factory()->create();
        $secretaria->assignRole('Secretaria');
        $secretaria->givePermissionTo('boletines.gestionar');
        
        $boletin = Boletin::factory()->create();
        
        $this->actingAs($secretaria)
            ->assertCan('update', $boletin);
    }

    /** @test */
    public function solo_director_subdirector_pueden_eliminar_boletin()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo('boletines.gestionar');
        
        $boletin = Boletin::factory()->create();
        
        $this->actingAs($director)
            ->assertCan('delete', $boletin);
        
        $this->actingAs($docenteUser)
            ->assertCant('delete', $boletin);
    }
}