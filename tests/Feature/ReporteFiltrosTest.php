<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Services\ReporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteFiltrosTest extends TestCase
{
    use RefreshDatabase;

    protected AnioEscolar $anio;
    protected Modalidad $modalidadA;
    protected Modalidad $modalidadB;
    protected Aula $aulaA;
    protected Aula $aulaB;
    protected Docente $docenteA;
    protected Docente $docenteB;
    protected Alumno $alumnoA;
    protected Alumno $alumnoB;
    protected ReporteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ReporteService::class);

        $this->anio = AnioEscolar::factory()->create(['nombre' => '2026', 'activo' => true]);

        $this->modalidadA = Modalidad::factory()->create(['nombre' => 'Primaria Regular']);
        $this->modalidadB = Modalidad::factory()->create(['nombre' => 'Secundaria Regular']);

        $gradoA = Grado::factory()->create(['nombre' => '1ro', 'modalidad_id' => $this->modalidadA->id]);
        $gradoB = Grado::factory()->create(['nombre' => '7mo', 'modalidad_id' => $this->modalidadB->id]);

        $this->aulaA = Aula::factory()->create([
            'modalidad_id' => $this->modalidadA->id,
            'grado_id' => $gradoA->id,
            'anio_escolar_id' => $this->anio->id,
            'turno' => 'Matutino',
        ]);
        $this->aulaB = Aula::factory()->create([
            'modalidad_id' => $this->modalidadB->id,
            'grado_id' => $gradoB->id,
            'anio_escolar_id' => $this->anio->id,
            'turno' => 'Vespertino',
        ]);

        $asigMat = Asignatura::factory()->create(['nombre' => 'Matemática']);
        $asigLen = Asignatura::factory()->create(['nombre' => 'Lengua y Literatura']);

        $this->docenteA = Docente::factory()->create();
        $this->docenteB = Docente::factory()->create();

        AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aulaA->id,
            'asignatura_id' => $asigMat->id,
            'docente_id' => $this->docenteA->id,
            'anio_escolar_id' => $this->anio->id,
        ]);
        AulaAsignaturaDocente::factory()->create([
            'aula_id' => $this->aulaB->id,
            'asignatura_id' => $asigLen->id,
            'docente_id' => $this->docenteB->id,
            'anio_escolar_id' => $this->anio->id,
        ]);

        $this->alumnoA = Alumno::factory()->create(['nombre_completo' => 'Ana Primaria']);
        $this->alumnoB = Alumno::factory()->create(['nombre_completo' => 'Beto Secundaria']);

        Matricula::factory()->create([
            'alumno_id' => $this->alumnoA->id,
            'aula_id' => $this->aulaA->id,
            'anio_escolar_id' => $this->anio->id,
            'estado' => 'activo',
        ]);
        Matricula::factory()->create([
            'alumno_id' => $this->alumnoB->id,
            'aula_id' => $this->aulaB->id,
            'anio_escolar_id' => $this->anio->id,
            'estado' => 'activo',
        ]);
    }

    public function test_notas_globales_filtra_por_docente(): void
    {
        $res = $this->service->notasGlobalesPorAlumno([
            'anio_escolar_id' => $this->anio->id,
            'docente_id' => $this->docenteA->id,
        ]);

        $this->assertCount(1, $res['filas']);
        $this->assertSame('Ana Primaria', $res['filas'][0]['alumno']);
        $this->assertSame('Matemática', $res['asignaturas']->first()->nombre);
    }

    public function test_notas_globales_filtra_por_modalidad(): void
    {
        $res = $this->service->notasGlobalesPorAlumno([
            'anio_escolar_id' => $this->anio->id,
            'modalidad_id' => $this->modalidadB->id,
        ]);

        $this->assertCount(1, $res['filas']);
        $this->assertSame('Beto Secundaria', $res['filas'][0]['alumno']);
        $this->assertSame('Lengua y Literatura', $res['asignaturas']->first()->nombre);
    }

    public function test_estadisticas_asistencia_filtra_por_modalidad(): void
    {
        $filas = $this->service->estadisticasAsistencia([
            'anio_escolar_id' => $this->anio->id,
            'modalidad_id' => $this->modalidadA->id,
        ]);

        $this->assertCount(1, $filas);
        $this->assertSame('Primaria Regular', $filas[0]['modalidad']);
    }

    public function test_control_notas_filtra_por_aula(): void
    {
        $filas = $this->service->controlNotas([
            'anio_escolar_id' => $this->anio->id,
            'aula_id' => $this->aulaB->id,
            'tipo' => '',
        ]);

        $this->assertCount(1, $filas);
        $this->assertSame($this->aulaB->nombre, $filas[0]['aula']);
    }

    public function test_notas_pendientes_filtra_por_modalidad(): void
    {
        $pendientes = $this->service->notasPendientes([
            'anio_escolar_id' => $this->anio->id,
            'modalidad_id' => $this->modalidadB->id,
        ]);

        $this->assertCount(1, $pendientes);
        $this->assertSame('Beto Secundaria', $pendientes->first()->alumno->nombre_completo);
    }
}
