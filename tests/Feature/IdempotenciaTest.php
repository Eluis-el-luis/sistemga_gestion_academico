<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Asignatura;
use App\Models\AsistenciaAsignatura;
use App\Models\BloqueHorario;
use App\Models\CorteEvaluativo;
use App\Models\Grado;
use App\Models\IndicadorLogro;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdempotenciaTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $docenteAsignatura;
    protected Aula $aula;
    protected AulaAsignaturaDocente $asignacion;
    protected Matricula $matricula;
    protected CorteEvaluativo $corte;
    protected \App\Models\ActividadEvaluativa $actividad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\Seeders\PermisoSeeder']);
        
        // Crear roles de catálogo directamente
        $roles = ['Director', 'Subdirector', 'Coordinador', 'Docente Guia', 'Docente por Asignatura', 'Gestor de Usuarios', 'Alumno'];
        foreach ($roles as $nombre) {
            Rol::firstOrCreate(['nombre' => $nombre]);
        }

        $rolDocenteAsignatura = Rol::where('nombre', 'Docente por Asignatura')->first()->id;
        $this->docenteAsignatura = Usuario::factory()->create(['rol_id' => $rolDocenteAsignatura]);
        $this->docenteAsignatura->assignRole('Docente por Asignatura');

        $docenteModel = \App\Models\Docente::factory()->create(['usuario_id' => $this->docenteAsignatura->id]);

        // Crear estructura académica mínima
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

        $this->corte = CorteEvaluativo::factory()->create([
            'anio_escolar_id' => $anio->id,
        ]);

        // Crear actividad evaluativa asociada (necesaria para el nuevo flujo de notas)
        $this->actividad = \App\Models\ActividadEvaluativa::factory()->create([
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
            'nombre' => 'Examen',
            'puntaje_maximo' => 100,
        ]);

        // Crear bloque horario para la modalidad (necesario para asistencia por asignatura)
        BloqueHorario::factory()->create([
            'modalidad_id' => $modalidad->id,
            'es_recreo' => false,
        ]);
    }

    
    public function testnota_updateOrCreate_es_idempotente(): void
    {
        $this->actingAs($this->docenteAsignatura);

        // Primera creación (formato nuevo: notas[matricula_id][actividad_id])
        $response1 = $this->post(route('academico.notas.store', $this->asignacion), [
            'corte_evaluativo_id' => $this->corte->id,
            'notas' => [
                $this->matricula->id => [
                    $this->actividad->id => 85,
                ],
            ],
        ]);
        $response1->assertSessionHas('success');

        $nota1 = Nota::where('matricula_id', $this->matricula->id)
            ->where('aula_asignatura_docente_id', $this->asignacion->id)
            ->where('corte_evaluativo_id', $this->corte->id)
            ->first();
        $this->assertEquals(85, $nota1->nota_cuantitativa);
        $this->assertEquals(1, Nota::count());

        // Segunda actualización (mismo key) - debe actualizar, no duplicar
        $response2 = $this->post(route('academico.notas.store', $this->asignacion), [
            'corte_evaluativo_id' => $this->corte->id,
            'notas' => [
                $this->matricula->id => [
                    $this->actividad->id => 90,
                ],
            ],
        ]);
        $response2->assertSessionHas('success');

        $nota2 = Nota::where('matricula_id', $this->matricula->id)
            ->where('aula_asignatura_docente_id', $this->asignacion->id)
            ->where('corte_evaluativo_id', $this->corte->id)
            ->first();
        $this->assertEquals(90, $nota2->nota_cuantitativa);
        $this->assertEquals(1, Nota::count(), 'Solo debe existir un registro (idempotencia)');
    }

    
    public function testasistencia_asignatura_updateOrCreate_es_idempotente(): void
    {
        $this->actingAs($this->docenteAsignatura);

        $bloque = BloqueHorario::where('es_recreo', false)->first();
        $fecha = Carbon::today()->toDateString();

        // Primera creación
        $response1 = $this->post(route('academico.asistencia.asignatura.store', $this->asignacion), [
            'fecha' => $fecha,
            'matricula_id' => $this->matricula->id,
            'bloque_horario_id' => $bloque->id,
            'estado_incidencia' => 'Fuga',
            'observacion' => 'Primera',
        ]);
        $response1->assertSessionHas('success');

        $incidencia1 = AsistenciaAsignatura::where('matricula_id', $this->matricula->id)
            ->where('asignatura_id', $this->asignacion->asignatura_id)
            ->where('bloque_horario_id', $bloque->id)
            ->where('fecha', $fecha)
            ->first();
        $this->assertEquals('Fuga', $incidencia1->estado_incidencia);
        $this->assertEquals(1, AsistenciaAsignatura::count());

        // Segunda actualización (mismo key) - debe actualizar, no duplicar
        $response2 = $this->post(route('academico.asistencia.asignatura.store', $this->asignacion), [
            'fecha' => $fecha,
            'matricula_id' => $this->matricula->id,
            'bloque_horario_id' => $bloque->id,
            'estado_incidencia' => 'Llegada Tardía',
            'observacion' => 'Actualizada',
        ]);
        $response2->assertSessionHas('success');

        $incidencia2 = AsistenciaAsignatura::where('matricula_id', $this->matricula->id)
            ->where('asignatura_id', $this->asignacion->asignatura_id)
            ->where('bloque_horario_id', $bloque->id)
            ->where('fecha', $fecha)
            ->first();
        $this->assertEquals('Llegada Tardía', $incidencia2->estado_incidencia);
        $this->assertEquals(1, AsistenciaAsignatura::count(), 'Solo debe existir una incidencia (idempotencia)');
    }
}