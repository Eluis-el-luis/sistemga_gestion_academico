<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Asignatura;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $director;
    protected Usuario $docenteAsignatura;

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

        // Estructura mínima
        $modalidad = Modalidad::factory()->create(['nombre' => 'Primaria Regular']);
        $grado = Grado::factory()->create(['nombre' => '1ro', 'modalidad_id' => $modalidad->id]);
        $anio = AnioEscolar::factory()->create(['nombre' => '2026', 'activo' => true]);
        $asignatura = Asignatura::factory()->create(['nombre' => 'Matemática', 'area' => 'Matemática']);

        $aula = Aula::factory()->create([
            'modalidad_id' => $modalidad->id,
            'grado_id' => $grado->id,
            'anio_escolar_id' => $anio->id,
            'turno' => 'Matutino',
        ]);

        $docenteModel = Docente::factory()->create(['usuario_id' => $this->docenteAsignatura->id]);

        AulaAsignaturaDocente::factory()->create([
            'aula_id' => $aula->id,
            'asignatura_id' => $asignatura->id,
            'docente_id' => $docenteModel->id,
            'anio_escolar_id' => $anio->id,
        ]);

        $alumno = Alumno::factory()->create();
        Matricula::factory()->create([
            'alumno_id' => $alumno->id,
            'aula_id' => $aula->id,
            'anio_escolar_id' => $anio->id,
            'estado' => 'activo',
        ]);
    }

    
    public function testdirector_puede_acceder_centro_reportes(): void
    {
        $this->actingAs($this->director)
            ->get(route('academico.reportes.index'))
            ->assertOk();
    }

    
    public function testdirector_puede_ver_todos_los_reportes(): void
    {
        $rutas = [
            'academico.reportes.control-notas',
            'academico.reportes.notas-globales',
            'academico.reportes.notas-pendientes',
            'academico.reportes.asistencia-global',
            'academico.reportes.estadisticas-asistencia',
            'academico.reportes.asistencia-seccion-dia',
            'academico.reportes.asistencia-seccion-rango',
            'academico.reportes.asistencia-estudiante',
            'academico.reportes.notas-por-asignatura',
            'academico.reportes.historial-estudiante',
            'academico.reportes.mined',
            'academico.reportes.estudiantes',
            'academico.reportes.padres',
        ];

        foreach ($rutas as $ruta) {
            $this->actingAs($this->director)
                ->get(route($ruta))
                ->assertOk();
        }
    }

    
    public function testdocente_por_asignatura_no_puede_acceder_reportes(): void
    {
        $this->actingAs($this->docenteAsignatura)
            ->get(route('academico.reportes.index'))
            ->assertStatus(403);
    }
}