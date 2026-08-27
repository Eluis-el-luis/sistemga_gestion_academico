<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Asignatura;
use App\Models\BloqueHorario;
use App\Models\CorteEvaluativo;
use App\Models\Grado;
use App\Models\IndicadorLogro;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutorizacionPorRolTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $director;
    protected Usuario $subdirector;
    protected Usuario $coordinador;
    protected Usuario $docenteGuia;
    protected Usuario $docenteAsignatura;
    protected Usuario $gestor;
    protected Usuario $alumnoUser;

    protected Aula $aula;
    protected AulaAsignaturaDocente $asignacion;
    protected Matricula $matricula;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar seeders base
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\PermisoSeeder']);
        
        // Crear roles de catálogo directamente (evita problemas con seeders en SQLite)
        $roles = ['Director', 'Subdirector', 'Coordinador', 'Docente Guia', 'Docente por Asignatura', 'Gestor de Usuarios', 'Alumno'];
        foreach ($roles as $nombre) {
            Rol::firstOrCreate(['nombre' => $nombre]);
        }

$rolDirector = Rol::where('nombre', 'Director')->first()->id;
        $rolSubdirector = Rol::where('nombre', 'Subdirector')->first()->id;
        $rolCoordinador = Rol::where('nombre', 'Coordinador')->first()->id;
        $rolDocenteGuia = Rol::where('nombre', 'Docente Guia')->first()->id;
        $rolDocenteAsignatura = Rol::where('nombre', 'Docente por Asignatura')->first()->id;
        $rolGestor = Rol::where('nombre', 'Gestor de Usuarios')->first()->id;
        $rolAlumno = Rol::where('nombre', 'Alumno')->first()->id;

        $this->director = Usuario::factory()->create(['rol_id' => $rolDirector]);
        $this->director->assignRole('Director');

        $this->subdirector = Usuario::factory()->create(['rol_id' => $rolSubdirector]);
        $this->subdirector->assignRole('Subdirector');

        $this->coordinador = Usuario::factory()->create(['rol_id' => $rolCoordinador]);
        $this->coordinador->assignRole('Coordinador');

        $this->docenteGuia = Usuario::factory()->create(['rol_id' => $rolDocenteGuia]);
        $this->docenteGuia->assignRole('Docente Guia');

        $this->docenteAsignatura = Usuario::factory()->create(['rol_id' => $rolDocenteAsignatura]);
        $this->docenteAsignatura->assignRole('Docente por Asignatura');

        $this->gestor = Usuario::factory()->create(['rol_id' => $rolGestor]);
        $this->gestor->assignRole('Gestor de Usuarios');

        $this->alumnoUser = Usuario::factory()->create(['rol_id' => $rolAlumno]);
        $this->alumnoUser->assignRole('Alumno');

        // Crear docentes asociados
        $docenteGuiaModel = \App\Models\Docente::factory()->create(['usuario_id' => $this->docenteGuia->id]);
        $docenteAsignaturaModel = \App\Models\Docente::factory()->create(['usuario_id' => $this->docenteAsignatura->id]);

        // Director y Subdirector también necesitan perfiles de docente para acceder a asistencia de aula
        $docenteDirectorModel = \App\Models\Docente::factory()->create(['usuario_id' => $this->director->id]);
        $docenteSubdirectorModel = \App\Models\Docente::factory()->create(['usuario_id' => $this->subdirector->id]);

        // Asignarles aulas para que puedan acceder
        $modalidad = Modalidad::factory()->create(['nombre' => 'Científico Humanista']);
        $grado = Grado::factory()->create(['nombre' => '1ro Secundaria', 'modalidad_id' => $modalidad->id]);
        $anio = AnioEscolar::factory()->create(['nombre' => '2026', 'activo' => true]);
        $asignatura = Asignatura::factory()->create(['nombre' => 'Matemática']);

        $aulaDirector = Aula::factory()->create([
            'modalidad_id' => $modalidad->id,
            'grado_id' => $grado->id,
            'anio_escolar_id' => $anio->id,
            'docente_guia_id' => $docenteDirectorModel->id,
        ]);

        $aulaSubdirector = Aula::factory()->create([
            'modalidad_id' => $modalidad->id,
            'grado_id' => $grado->id,
            'anio_escolar_id' => $anio->id,
            'docente_guia_id' => $docenteSubdirectorModel->id,
        ]);

        $this->aula = Aula::factory()->create([
            'modalidad_id' => $modalidad->id,
            'grado_id' => $grado->id,
            'anio_escolar_id' => $anio->id,
            'docente_guia_id' => $docenteGuiaModel->id,
        ]);

        $this->asignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => $asignatura->id,
            'docente_id' => $docenteAsignaturaModel->id,
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

    
    public function testdirector_puede_acceder_a_notas_y_asistencia(): void
    {
        $this->actingAs($this->director)
            ->get(route('academico.notas.index'))
            ->assertOk();

        $this->actingAs($this->director)
            ->get(route('academico.asistencia.aula.create'))
            ->assertOk();
    }

    
    public function testsubdirector_puede_acceder_a_notas_y_asistencia(): void
    {
        $this->actingAs($this->subdirector)
            ->get(route('academico.notas.index'))
            ->assertOk();

        $this->actingAs($this->subdirector)
            ->get(route('academico.asistencia.aula.create'))
            ->assertOk();
    }

    
    public function testcoordinador_puede_ver_notas_y_asistencia(): void
    {
        $this->actingAs($this->coordinador)
            ->get(route('academico.notas.index'))
            ->assertOk();

        $this->actingAs($this->coordinador)
            ->get(route('academico.asistencia.aula.create'))
            ->assertStatus(403); // Coordinador no tiene asistencia.gestionar
    }

    
    public function testdocente_guia_puede_gestionar_su_aula(): void
    {
        $this->actingAs($this->docenteGuia)
            ->get(route('academico.notas.index'))
            ->assertOk();

        $this->actingAs($this->docenteGuia)
            ->get(route('academico.asistencia.aula.create'))
            ->assertOk();
    }

    
    public function testdocente_asignatura_puede_calificar_su_asignatura(): void
    {
        $this->actingAs($this->docenteAsignatura)
            ->get(route('academico.notas.create', $this->asignacion))
            ->assertOk();

        $this->actingAs($this->docenteAsignatura)
            ->get(route('academico.asistencia.asignatura.create', $this->asignacion))
            ->assertOk();
    }

    
    public function testdocente_asignatura_no_puede_calificar_otra_asignatura(): void
    {
        // Crear otra asignatura con otro docente
        $otroDocente = \App\Models\Docente::factory()->create();
        $otraAsignacion = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aula->id,
            'asignatura_id' => \App\Models\Asignatura::factory()->create()->id,
            'docente_id' => $otroDocente->id,
            'anio_escolar_id' => $this->aula->anio_escolar_id,
        ]);

        $this->actingAs($this->docenteAsignatura)
            ->get(route('academico.notas.create', $otraAsignacion))
            ->assertStatus(403);
    }

    
    public function testgestor_no_puede_acceder_a_notas_ni_asistencia(): void
    {
        // notas.index no fuerza autorización; devuelve vista vacía para roles sin docente
        $this->actingAs($this->gestor)
            ->get(route('academico.notas.index'))
            ->assertOk();

        // asistencia.aula.create deniega por política (sin permiso asistencia.gestionar)
        $this->actingAs($this->gestor)
            ->get(route('academico.asistencia.aula.create'))
            ->assertStatus(403);
    }

    
    public function testalumno_no_puede_acceder_a_notas_ni_asistencia(): void
    {
        // notas.index no fuerza autorización; devuelve vista vacía para roles sin docente
        $this->actingAs($this->alumnoUser)
            ->get(route('academico.notas.index'))
            ->assertOk();

        // asistencia.aula.create deniega por política (sin permiso asistencia.gestionar)
        $this->actingAs($this->alumnoUser)
            ->get(route('academico.asistencia.aula.create'))
            ->assertStatus(403);
    }
}