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
use App\Models\Nota;
use App\Models\CorteEvaluativo;
use App\Models\CorteCerrado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear estructura base
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
        
        // Crear corte evaluativo
        $this->corte = CorteEvaluativo::factory()->create([
            'anio_escolar_id' => $this->anioEscolar->id,
            'numero' => 1,
            'nombre' => 'Primer Corte',
        ]);
    }

    /** @test */
    public function director_puede_ver_cualquier_nota()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $nota = Nota::factory()->create([
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($director)
            ->assertCan('viewAny', Nota::class)
            ->assertCan('view', $nota);
    }

    /** @test */
    public function subdirector_puede_ver_cualquier_nota()
    {
        $subdirector = Usuario::factory()->create();
        $subdirector->assignRole('Subdirector');
        
        $nota = Nota::factory()->create([
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($subdirector)
            ->assertCan('viewAny', Nota::class)
            ->assertCan('view', $nota);
    }

    /** @test */
    public function docente_guia_puede_ver_notas_de_su_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo(['notas.ver', 'notas.gestionar']);
        
        $docente = Docente::factory()->create([
            'usuario_id' => $docenteUser->id,
        ]);
        
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $this->asignatura->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $nota = Nota::factory()->create([
            'matricula_id' => $matricula->id,
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('view', $nota);
    }

    /** @test */
    public function docente_guia_NO_puede_ver_notas_de_otra_aula()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo(['notas.ver', 'notas.gestionar']);
        
        $docente = Docente::factory()->create([
            'usuario_id' => $docenteUser->id,
        ]);
        
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        // Otra aula sin docente guía asignado
        $otraAula = Aula::factory()->create([
            'grado_id' => $this->grado->id,
            'modalidad_id' => $this->modalidad->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'nombre' => 'B',
            'turno' => 'Mañana',
        ]);
        
        $otraAsignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $otraAula->id,
            'asignatura_id' => $this->asignatura->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $otraAula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $nota = Nota::factory()->create([
            'matricula_id' => $matricula->id,
            'aula_asignatura_docente_id' => $otraAsignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCant('view', $nota);
    }

    /** @test */
    public function docente_asignatura_puede_ver_notas_de_su_asignatura()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente por Asignatura');
        $docenteUser->givePermissionTo(['notas.ver', 'notas.gestionar']);
        
        $docente = Docente::factory()->create([
            'usuario_id' => $docenteUser->id,
        ]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $this->asignatura->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $nota = Nota::factory()->create([
            'matricula_id' => $matricula->id,
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('view', $nota);
    }

    /** @test */
    public function docente_asignatura_NO_puede_ver_notas_de_otra_asignatura()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente por Asignatura');
        $docenteUser->givePermissionTo(['notas.ver', 'notas.gestionar']);
        
        $docente = Docente::factory()->create([
            'usuario_id' => $docenteUser->id,
        ]);
        
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
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $nota = Nota::factory()->create([
            'matricula_id' => $matricula->id,
            'aula_asignatura_docente_id' => $otraAsignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCant('view', $nota);
    }

    /** @test */
    public function director_puede_actualizar_nota_si_corte_no_bloqueado()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $nota = Nota::factory()->create([
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($director)
            ->assertCan('update', $nota);
    }

    /** @test */
    public function director_NO_puede_actualizar_nota_si_corte_bloqueado()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $nota = Nota::factory()->create([
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        CorteCerrado::factory()->create([
            'aula_asignatura_docente_id' => $nota->aula_asignatura_docente_id,
            'corte_evaluativo_id' => $this->corte->id,
            'bloqueado' => true,
        ]);
        
        $this->actingAs($director)
            ->assertCant('update', $nota);
    }

    /** @test */
    public function docente_guia_puede_actualizar_nota_de_su_aula_si_corte_no_bloqueado()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo('notas.gestionar');
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $nota = Nota::factory()->create([
            'matricula_id' => $matricula->id,
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCan('update', $nota);
    }

    /** @test */
    public function docente_guia_NO_puede_actualizar_nota_si_corte_bloqueado()
    {
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo('notas.gestionar');
        
        $docente = Docente::factory()->create(['usuario_id' => $docenteUser->id]);
        $this->aula->update(['docente_guia_id' => $docente->id]);
        
        $asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioEscolar->id,
        ]);
        
        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula->id,
            'anio_escolar_id' => $this->anioEscolar->id,
            'estado' => 'activo',
        ]);
        
        $nota = Nota::factory()->create([
            'matricula_id' => $matricula->id,
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        CorteCerrado::factory()->create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
            'bloqueado' => true,
        ]);
        
        $this->actingAs($docenteUser)
            ->assertCant('update', $nota);
    }

    /** @test */
    public function solo_director_subdirector_pueden_eliminar_nota()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('Director');
        
        $docenteUser = Usuario::factory()->create();
        $docenteUser->assignRole('Docente Guia');
        $docenteUser->givePermissionTo('notas.gestionar');
        
        $nota = Nota::factory()->create([
            'corte_evaluativo_id' => $this->corte->id,
        ]);
        
        $this->actingAs($director)
            ->assertCan('delete', $nota);
        
        $this->actingAs($docenteUser)
            ->assertCant('delete', $nota);
    }

    /** @test */
    public function usuario_sin_permiso_notas_gestionar_no_puede_crear_nota()
    {
        $user = Usuario::factory()->create();
        $user->assignRole('Docente por Asignatura');
        // Sin permiso notas.gestionar
        
        $this->actingAs($user)
            ->assertCant('create', Nota::class);
    }
}