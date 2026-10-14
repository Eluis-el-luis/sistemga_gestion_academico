<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\ExamenReparacion;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;
use App\Services\ReporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RendimientoMined2026Test extends TestCase
{
    use RefreshDatabase;

    protected array $datos;
    protected ReporteService $service;
    protected CorteEvaluativo $corte;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ReporteService::class);

        $anio = AnioEscolar::factory()->create(['nombre' => '2026', 'activo' => true]);
        $modalidad = Modalidad::factory()->create(['nombre' => 'Primaria Regular']);
        $grado = Grado::factory()->create(['nombre' => '5to', 'modalidad_id' => $modalidad->id]);
        $aula = Aula::factory()->create([
            'modalidad_id' => $modalidad->id,
            'grado_id' => $grado->id,
            'anio_escolar_id' => $anio->id,
        ]);
        $docente = Docente::factory()->create();

        $asig1 = Asignatura::factory()->create(['nombre' => 'Matemática']);
        $asig2 = Asignatura::factory()->create(['nombre' => 'Español']);

        $asign1 = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $aula->id, 'asignatura_id' => $asig1->id,
            'docente_id' => $docente->id, 'anio_escolar_id' => $anio->id,
        ]);
        $asign2 = AulaAsignaturaDocente::factory()->create([
            'aula_id' => $aula->id, 'asignatura_id' => $asig2->id,
            'docente_id' => $docente->id, 'anio_escolar_id' => $anio->id,
        ]);

        $this->corte = CorteEvaluativo::factory()->create([
            'anio_escolar_id' => $anio->id, 'numero' => 3, 'semestre' => 2,
            'fecha_inicio' => '2026-09-16', 'fecha_fin' => '2026-11-30',
        ]);

        // Alumno M: aprobado (90/90) → AA
        $m1 = Matricula::factory()->create(['alumno_id' => Alumno::factory()->create(['sexo' => 'M'])->id, 'aula_id' => $aula->id, 'anio_escolar_id' => $anio->id, 'estado' => 'activo']);
        Nota::create(['matricula_id' => $m1->id, 'aula_asignatura_docente_id' => $asign1->id, 'corte_evaluativo_id' => $this->corte->id, 'nota_cuantitativa' => 90]);
        Nota::create(['matricula_id' => $m1->id, 'aula_asignatura_docente_id' => $asign2->id, 'corte_evaluativo_id' => $this->corte->id, 'nota_cuantitativa' => 90]);

        // Alumna F: aplazada de 1 (50/80) → promedio 65 → AE
        $m2 = Matricula::factory()->create(['alumno_id' => Alumno::factory()->create(['sexo' => 'F'])->id, 'aula_id' => $aula->id, 'anio_escolar_id' => $anio->id, 'estado' => 'activo']);
        Nota::create(['matricula_id' => $m2->id, 'aula_asignatura_docente_id' => $asign1->id, 'corte_evaluativo_id' => $this->corte->id, 'nota_cuantitativa' => 50]);
        Nota::create(['matricula_id' => $m2->id, 'aula_asignatura_docente_id' => $asign2->id, 'corte_evaluativo_id' => $this->corte->id, 'nota_cuantitativa' => 80]);

        // Alumna F: sin notas → NO EVALUADO
        $m3 = Matricula::factory()->create(['alumno_id' => Alumno::factory()->create(['sexo' => 'F'])->id, 'aula_id' => $aula->id, 'anio_escolar_id' => $anio->id, 'estado' => 'activo']);

        // Alumno M retirado: cuenta en MI pero no en MA
        Matricula::factory()->create(['alumno_id' => Alumno::factory()->create(['sexo' => 'M'])->id, 'aula_id' => $aula->id, 'anio_escolar_id' => $anio->id, 'estado' => 'retirado']);

        // Periodo extra: examen de reparación del alumno 1 dentro del rango del corte
        ExamenReparacion::create([
            'matricula_id' => $m1->id,
            'asignatura_id' => $asig1->id,
            'nota_obtenida' => 70,
            'fecha' => '2026-10-15',
            'resultado' => 'aprobado',
        ]);

        $this->datos = $this->service->rendimientoCorteMined([
            'anio_escolar_id' => $anio->id,
            'corte_evaluativo_id' => $this->corte->id,
        ]);
    }

    protected function fila(): array
    {
        return $this->datos['secciones'][0]['filas'][0];
    }

    public function test_columnas_as_f_y_totales_del_formato_2026(): void
    {
        $f = $this->fila();

        // AS = ambos sexos (incluye F)
        $this->assertSame(4, $f['mi_as']);      // 3 activos + 1 retirado
        $this->assertSame(2, $f['mi_f']);       // 2 femeninas
        $this->assertSame(3, $f['ma_as']);      // solo activos
        $this->assertSame(2, $f['ma_f']);
        $this->assertSame(75.0, $f['perm_as']); // 3/4
        $this->assertSame(100.0, $f['perm_f']); // 2/2
        $this->assertSame(1, $f['aprob_as']);
        $this->assertSame(0, $f['aprob_f']);
        $this->assertSame(1, $f['aplaz12_as']); // aplazado de 1 → columna 1-2
        $this->assertSame(1, $f['aplaz12_f']);
        $this->assertSame(0, $f['aplaz3_as']);
        $this->assertSame(2, $f['eval_oficial_as']); // m1 y m2 tienen notas
        $this->assertSame(1, $f['eval_oficial_f']);  // solo m2 es F
        $this->assertSame(1, $f['eval_extra_as']); // examen de reparación
        $this->assertSame(1, $f['no_evaluado_as']); // MA sin notas
        $this->assertSame(1, $f['no_evaluado_f']);
        $this->assertSame(0, $f['asist_ref_as']);   // sin módulo → 0
        $this->assertSame(0, $f['maest_ref_as']);
    }

    public function test_escala_con_nueva_banda_ae_2026(): void
    {
        $f = $this->fila();

        $this->assertSame(1, $f['aa_as']); // 90 → AA
        $this->assertSame(1, $f['ae_as']); // 65 → AE (antes AF)
        $this->assertSame(1, $f['ae_f']);
        $this->assertSame(0, $f['ai_as']);
        $this->assertSame(2, $f['valor_as']); // AA+AS+AE+AI
        $this->assertSame(1, $f['valor_f']);
    }

    public function test_total_por_modalidad_se_recalcula(): void
    {
        $total = $this->datos['secciones'][0]['total'];

        $this->assertSame('TOTAL', $total['grado']);
        $this->assertSame(4, $total['mi_as']);
        $this->assertSame(3, $total['ma_as']);
        $this->assertSame(75.0, $total['perm_as']);
    }
}
