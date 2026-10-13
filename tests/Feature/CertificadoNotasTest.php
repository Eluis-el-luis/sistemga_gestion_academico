<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;
use App\Services\ReporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificadoNotasTest extends TestCase
{
    use RefreshDatabase;

    protected AnioEscolar $anioAnterior;
    protected AnioEscolar $anioActual;
    protected Modalidad $modalidad;
    protected Grado $grado4;
    protected Grado $grado5;
    protected Grado $grado6;
    protected Alumno $alumno;
    protected Matricula $matricula4;
    protected Matricula $matricula5;
    protected ReporteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ReporteService::class);

        $this->anioAnterior = AnioEscolar::factory()->create(['nombre' => '2025', 'activo' => false]);
        $this->anioActual = AnioEscolar::factory()->create(['nombre' => '2026', 'activo' => true]);

        $this->modalidad = Modalidad::factory()->create(['nombre' => 'Primaria Regular']);

        $this->grado4 = Grado::factory()->create(['nombre' => '4to', 'modalidad_id' => $this->modalidad->id]);
        $this->grado5 = Grado::factory()->create(['nombre' => '5to', 'modalidad_id' => $this->modalidad->id]);
        $this->grado6 = Grado::factory()->create(['nombre' => '6to', 'modalidad_id' => $this->modalidad->id]);

        $docente = Docente::factory()->create();

        $aula4 = Aula::factory()->create([
            'modalidad_id' => $this->modalidad->id,
            'grado_id' => $this->grado4->id,
            'anio_escolar_id' => $this->anioAnterior->id,
        ]);
        $aula5 = Aula::factory()->create([
            'modalidad_id' => $this->modalidad->id,
            'grado_id' => $this->grado5->id,
            'anio_escolar_id' => $this->anioActual->id,
        ]);

        $asigMat = Asignatura::factory()->create(['nombre' => 'Matemática']);
        $asigLen = Asignatura::factory()->create(['nombre' => 'Lengua y Literatura']);

        $asignacion4 = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $aula4->id,
            'asignatura_id' => $asigMat->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioAnterior->id,
        ]);
        $asignacion5 = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $aula5->id,
            'asignatura_id' => $asigMat->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioActual->id,
        ]);
        $asignacion5Len = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $aula5->id,
            'asignatura_id' => $asigLen->id,
            'docente_id' => $docente->id,
            'anio_escolar_id' => $this->anioActual->id,
        ]);

        $this->alumno = Alumno::factory()->create(['nombre_completo' => 'Estudiante Historial']);

        // 4to en 2025: reprobado (notas < 60) — debe aparecer igual
        $cortes2025 = collect();
        $matricula4IdesMatricula = Matricula::factory()->create([
            'alumno_id' => $this->alumno->id,
            'aula_id' => $aula4->id,
            'anio_escolar_id' => $this->anioAnterior->id,
            'estado' => 'activo',
        ]);
        $this->matricula4 = $matricula4IdesMatricula;

        for ($i = 1; $i <= 4; $i++) {
            $cortes2025->push(CorteEvaluativo::factory()->create([
                'anio_escolar_id' => $this->anioAnterior->id,
                'numero' => $i,
                'semestre' => $i <= 2 ? 1 : 2,
            ]));
        }
        foreach ($cortes2025 as $corte) {
            Nota::create([
                'matricula_id' => $this->matricula4->id,
                'aula_asignatura_docente_id' => $asignacion4->id,
                'corte_evaluativo_id' => $corte->id,
                'nota_cuantitativa' => 50,
            ]);
        }

        // 5to en 2026: notas buenas (90) en Matemática y 70 en Lengua
        $cortes2026 = collect();
        $this->matricula5 = Matricula::factory()->create([
            'alumno_id' => $this->alumno->id,
            'aula_id' => $aula5->id,
            'anio_escolar_id' => $this->anioActual->id,
            'estado' => 'activo',
        ]);
        for ($i = 1; $i <= 4; $i++) {
            $cortes2026->push(CorteEvaluativo::factory()->create([
                'anio_escolar_id' => $this->anioActual->id,
                'numero' => $i,
                'semestre' => $i <= 2 ? 1 : 2,
            ]));
        }
        foreach ($cortes2026 as $corte) {
            Nota::create([
                'matricula_id' => $this->matricula5->id,
                'aula_asignatura_docente_id' => $asignacion5->id,
                'corte_evaluativo_id' => $corte->id,
                'nota_cuantitativa' => 90,
            ]);
            Nota::create([
                'matricula_id' => $this->matricula5->id,
                'aula_asignatura_docente_id' => $asignacion5Len->id,
                'corte_evaluativo_id' => $corte->id,
                'nota_cuantitativa' => 70,
            ]);
        }
    }

    public function test_certificado_incluye_grado_seleccionado_y_el_anterior_incluso_si_reprobo(): void
    {
        $data = $this->service->certificadoNotas($this->alumno->id, $this->anioActual->id, $this->grado5->id);

        // 2 grados: anterior (4to) y seleccionado (5to)
        $this->assertCount(2, $data['grados']);
        $this->assertSame($this->grado4->id, $data['grados'][0]['grado']->id);
        $this->assertSame($this->grado5->id, $data['grados'][1]['grado']->id);

        // El grado seleccionado usa el año filtrado; el anterior usa su año histórico
        $this->assertSame($this->anioActual->id, $data['grados'][1]['anioEscolar']->id);
        $this->assertSame($this->anioAnterior->id, $data['grados'][0]['anioEscolar']->id);

        // NF del grado anterior (reprobado: 50) se muestra igual
        $this->assertSame(50, $data['grados'][0]['notas']['Matemática']['cuan']);
        $this->assertSame('AI', $data['grados'][0]['notas']['Matemática']['cua']);

        // NF del grado seleccionado
        $this->assertSame(90, $data['grados'][1]['notas']['Matemática']['cuan']);
        $this->assertSame('AA', $data['grados'][1]['notas']['Matemática']['cua']);
        $this->assertSame(70, $data['grados'][1]['notas']['Lengua y Literatura']['cuan']);
        $this->assertSame('AF', $data['grados'][1]['notas']['Lengua y Literatura']['cua']);

        // Unión de asignaturas incluye las de ambos grados
        $nombres = array_column($data['asignaturas'], 'nombre');
        $this->assertContains('Matemática', $nombres);
        $this->assertContains('Lengua y Literatura', $nombres);
    }

    public function test_limite_de_grados_es_configurable(): void
    {
        // Con límite 1 solo debe aparecer el grado seleccionado
        $data = $this->service->certificadoNotas($this->alumno->id, $this->anioActual->id, $this->grado5->id, 1);

        $this->assertCount(1, $data['grados']);
        $this->assertSame($this->grado5->id, $data['grados'][0]['grado']->id);

        // Sin matrícula en 6to, la cadena debe incluir 5to y 4to igualmente
        $data2 = $this->service->certificadoNotas($this->alumno->id, $this->anioActual->id, $this->grado6->id);

        $this->assertCount(2, $data2['grados']);
        $this->assertSame($this->grado5->id, $data2['grados'][0]['grado']->id);
        $this->assertSame($this->grado6->id, $data2['grados'][1]['grado']->id);
        // 6to no tiene matrícula del alumno: sin notas, pero la columna existe
        $this->assertNull($data2['grados'][1]['matricula']);
        $this->assertSame([], $data2['grados'][1]['notas']);
        // 5to sí muestra su historial
        $this->assertSame(90, $data2['grados'][0]['notas']['Matemática']['cuan']);
    }

    public function test_alumno_inexistente_devuelve_estructura_vacia(): void
    {
        $data = $this->service->certificadoNotas(99999, $this->anioActual->id, $this->grado5->id);

        $this->assertNull($data['alumno']);
        $this->assertSame([], $data['grados']);
        $this->assertSame([], $data['asignaturas']);
    }
}
