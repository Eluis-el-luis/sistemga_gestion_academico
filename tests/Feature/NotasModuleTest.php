<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Alumno;
use App\Models\ActividadEvaluativa;
use App\Models\AnioEscolar;
use App\Models\CorteCerrado;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Modalidad;
use App\Models\Nota;
use App\Models\NotaActividad;
use App\Models\Matricula;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 5 — Notas: autorización 'calificar' (titular/guía/tercero), límites de
 * actividades por tipo y peso del corte, recalculo al editar/eliminar
 * actividades, bloqueo por corte cerrado y edición de cortes evaluativos.
 */
class NotasModuleTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $director;
    protected Usuario $titular;
    protected Usuario $otroDocente;
    protected Aula $aula;
    protected AulaAsignaturaDocente $asignacion;
    protected CorteEvaluativo $corte;
    protected array $matriculas = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DatabaseSeeder']);

        $this->director = $this->crearUsuario('Director');

        // Titular de Matemática
        $this->titular = $this->crearUsuario('Docente por Asignatura');
        $docenteTitular = Docente::create([
            'usuario_id' => $this->titular->id,
            'codigo_unico_persona' => 'N5-TIT',
            'sexo' => 'F',
        ]);

        // Docente guía del aula (otra persona)
        $guia = $this->crearUsuario('Docente Guia');
        $docenteGuia = Docente::create([
            'usuario_id' => $guia->id,
            'codigo_unico_persona' => 'N5-GUIA',
            'sexo' => 'M',
        ]);

        // Otro docente SIN relación con la asignación
        $this->otroDocente = $this->crearUsuario('Docente por Asignatura');
        Docente::create([
            'usuario_id' => $this->otroDocente->id,
            'codigo_unico_persona' => 'N5-OTRO',
            'sexo' => 'M',
        ]);

        $primaria = Modalidad::where('nombre', 'Primaria Regular')->firstOrFail();
        $grado1ro = Grado::where('nombre', '1ro')->where('modalidad_id', $primaria->id)->firstOrFail();

        app(\App\Services\AulaService::class)->crearAulaConMalla([
            'nombre' => 'A',
            'grado_id' => $grado1ro->id,
            'modalidad_id' => $primaria->id,
            'turno' => 'Matutino',
            'docente_guia_id' => $docenteGuia->id,
            'anio_escolar_id' => AnioEscolar::where('nombre', '2026')->firstOrFail()->id,
            'cupo' => 30,
        ]);

        $this->aula = Aula::where('nombre', 'A')->firstOrFail();
        $matematica = \App\Models\Asignatura::where('nombre', 'Matemática')->firstOrFail();

        $this->asignacion = AulaAsignaturaDocente::where('aula_id', $this->aula->id)
            ->where('asignatura_id', $matematica->id)->firstOrFail();
        $this->asignacion->update(['docente_id' => $docenteTitular->id]);

        $this->corte = CorteEvaluativo::where('anio_escolar_id', $this->aula->anio_escolar_id)
            ->where('numero', 1)->firstOrFail();

        // Dos alumnos matriculados
        foreach (['Alumno Uno Notas', 'Alumno Dos Notas'] as $nombre) {
            $alumno = Alumno::create([
                'codigo_unico_persona' => 'N5-' . md5($nombre . microtime()),
                'nombre_completo' => $nombre,
                'sexo' => 'M',
                'fecha_nacimiento' => '2019-01-15',
                'direccion_domiciliar' => 'Prueba',
            ]);
            $this->matriculas[] = Matricula::create([
                'alumno_id' => $alumno->id,
                'aula_id' => $this->aula->id,
                'anio_escolar_id' => $this->aula->anio_escolar_id,
                'estado' => 'activo',
                'fecha_matricula' => now()->toDateString(),
            ]);
        }
    }

    private function crearUsuario(string $rol, array $extra = []): Usuario
    {
        $usuario = Usuario::factory()->create($extra);
        $usuario->assignRole($rol);

        return $usuario;
    }

    private function crearActividad(array $extra = []): ActividadEvaluativa
    {
        return ActividadEvaluativa::create(array_merge([
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
            'nombre' => 'Actividad ' . uniqid(),
            'tipo' => 'acumulado',
            'puntaje_maximo' => 20,
            'fecha' => now()->toDateString(),
        ], $extra));
    }

    private function registrarNota(Matricula $matricula, ActividadEvaluativa $actividad, float $puntos): Nota
    {
        NotaActividad::create([
            'matricula_id' => $matricula->id,
            'actividad_evaluativa_id' => $actividad->id,
            'nota_obtenida' => $puntos,
        ]);

        return app(\App\Services\NotaService::class)->registrarNotaFinal(
            $matricula->id,
            $this->asignacion->id,
            $this->corte->id,
            $puntos,
            100,
            null
        );
    }

    // ==================================================================
    // Autorización: ¿quién puede calificar?
    // ==================================================================

    public function test_titular_y_docente_guia_califican_un_tercero_no(): void
    {
        $payload = [
            'nombre' => 'Tarea 1',
            'tipo' => 'acumulado',
            'puntaje_maximo' => 20,
            'corte_evaluativo_id' => $this->corte->id,
        ];

        // El docente guía (dueño del aula) también puede (Regla B de la policy)
        $guia = Usuario::whereHas('docente', fn ($q) => $q->where('codigo_unico_persona', 'N5-GUIA'))->first();
        $this->actingAs($guia)
            ->post(route('academico.notas.actividades.store', $this->asignacion), $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        // Un docente ajeno a la asignación: prohibido
        $this->actingAs($this->otroDocente)
            ->post(route('academico.notas.actividades.store', $this->asignacion), $payload)
            ->assertForbidden();
    }

    // ==================================================================
    // Límites de actividades por tipo y peso del corte
    // ==================================================================

    public function test_limite_acumulado_del_corte(): void
    {
        $this->actingAs($this->titular)
            ->post(route('academico.notas.actividades.store', $this->asignacion), [
                'nombre' => 'Acumulada 1',
                'tipo' => 'acumulado',
                'puntaje_maximo' => 50,
                'corte_evaluativo_id' => $this->corte->id,
            ])
            ->assertSessionHas('success');

        // 50 + 20 = 70 > peso_acumulado (60): rechazada
        $this->actingAs($this->titular)
            ->post(route('academico.notas.actividades.store', $this->asignacion), [
                'nombre' => 'Acumulada 2',
                'tipo' => 'acumulado',
                'puntaje_maximo' => 20,
                'corte_evaluativo_id' => $this->corte->id,
            ])
            ->assertSessionHas('error');

        $this->assertSame(
            1,
            ActividadEvaluativa::where('aula_asignatura_docente_id', $this->asignacion->id)
                ->where('corte_evaluativo_id', $this->corte->id)
                ->where('tipo', 'acumulado')->count()
        );
    }

    public function test_limite_examen_del_corte(): void
    {
        $this->actingAs($this->titular)
            ->post(route('academico.notas.actividades.store', $this->asignacion), [
                'nombre' => 'Examen 1',
                'tipo' => 'examen',
                'puntaje_maximo' => 30,
                'corte_evaluativo_id' => $this->corte->id,
            ])
            ->assertSessionHas('success');

        // 30 + 20 = 50 > peso_examen (40): rechazada
        $this->actingAs($this->titular)
            ->post(route('academico.notas.actividades.store', $this->asignacion), [
                'nombre' => 'Examen 2',
                'tipo' => 'examen',
                'puntaje_maximo' => 20,
                'corte_evaluativo_id' => $this->corte->id,
            ])
            ->assertSessionHas('error');
    }

    public function test_actualizar_actividad_excede_limite(): void
    {
        $actividad = $this->crearActividad(['nombre' => 'Tarea A', 'puntaje_maximo' => 50]);
        $this->crearActividad(['nombre' => 'Tarea B', 'puntaje_maximo' => 5]);

        // Al actualizar Tarea A a 60: 5 (Tarea B) + 60 = 65 > 60: rechazada
        $this->actingAs($this->titular)
            ->put(route('academico.notas.actividades.update', [$this->asignacion, $actividad]), [
                'nombre' => 'Tarea A',
                'puntaje_maximo' => 60,
            ])
            ->assertSessionHas('error');

        $this->assertSame(50.0, (float) $actividad->fresh()->puntaje_maximo);
    }

    public function test_eliminar_actividad_borra_sus_notas_y_recalcula(): void
    {
        $actividad1 = $this->crearActividad(['nombre' => 'Tarea A', 'puntaje_maximo' => 20]);
        $actividad2 = $this->crearActividad(['nombre' => 'Tarea B', 'puntaje_maximo' => 20]);

        // Alumno 1: 10 + 10 puntos => NF 20
        $this->registrarNota($this->matriculas[0], $actividad1, 10);
        $this->registrarNota($this->matriculas[0], $actividad2, 10);

        $this->actingAs($this->titular)
            ->delete(route('academico.notas.actividades.destroy', [$this->asignacion, $actividad2]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('nota_actividad', ['actividad_evaluativa_id' => $actividad2->id]);

        // Recalculada: solo quedan 10 puntos de la Tarea A => NF 10
        $nota = Nota::where('matricula_id', $this->matriculas[0]->id)
            ->where('corte_evaluativo_id', $this->corte->id)->firstOrFail();
        $this->assertEqualsWithDelta(10, (float) $nota->nota_cuantitativa, 0.01);
    }

    // ==================================================================
    // Bloqueo por corte cerrado
    // ==================================================================

    public function test_no_se_guardan_notas_con_el_parcial_cerrado(): void
    {
        $actividad = $this->crearActividad(['nombre' => 'Tarea X', 'puntaje_maximo' => 20]);

        CorteCerrado::create([
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'corte_evaluativo_id' => $this->corte->id,
            'bloqueado' => true,
        ]);

        $this->actingAs($this->titular)
            ->post(route('academico.notas.store', $this->asignacion), [
                'corte_evaluativo_id' => $this->corte->id,
                'notas' => [
                    $this->matriculas[0]->id => [$actividad->id => 15],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(
            0,
            NotaActividad::where('actividad_evaluativa_id', $actividad->id)->count()
        );
    }

    public function test_guardar_notas_calcula_la_nota_final_escalada(): void
    {
        $actividad = $this->crearActividad(['nombre' => 'Tarea Y', 'puntaje_maximo' => 20]);

        $this->actingAs($this->titular)
            ->post(route('academico.notas.store', $this->asignacion), [
                'corte_evaluativo_id' => $this->corte->id,
                'notas' => [
                    $this->matriculas[0]->id => [$actividad->id => 15],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Escalado MINED: 15 puntos sobre el total oficial del corte (100) => 15 (AI)
        $nota = Nota::where('matricula_id', $this->matriculas[0]->id)
            ->where('corte_evaluativo_id', $this->corte->id)->firstOrFail();
        $this->assertEqualsWithDelta(15, (float) $nota->nota_cuantitativa, 0.01);
        $this->assertSame('AI', $nota->indicadorLogro->codigo);
    }

    // ==================================================================
    // Cortes evaluativos (pesos y fechas)
    // ==================================================================

    public function test_actualizar_corte_con_pesos_validos(): void
    {
        $this->actingAs($this->director)
            ->put(route('academico.cortes.update', $this->corte), [
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'peso_acumulado' => 70,
                'peso_examen' => 30,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('corte_evaluativo', [
            'id' => $this->corte->id,
            'peso_acumulado' => 70,
            'peso_examen' => 30,
        ]);
    }

    public function test_actualizar_corte_exige_suma_de_pesos_100(): void
    {
        $this->actingAs($this->director)
            ->put(route('academico.cortes.update', $this->corte), [
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-04-30',
                'peso_acumulado' => 70,
                'peso_examen' => 40,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('corte_evaluativo', [
            'id' => $this->corte->id,
            'peso_acumulado' => 70,
        ]);
    }

    public function test_actualizar_corte_valida_fechas(): void
    {
        $this->actingAs($this->director)
            ->put(route('academico.cortes.update', $this->corte), [
                'fecha_inicio' => '2026-04-30',
                'fecha_fin' => '2026-02-01', // anterior al inicio
                'peso_acumulado' => 60,
                'peso_examen' => 40,
            ])
            ->assertSessionHasErrors('fecha_fin');
    }
}
