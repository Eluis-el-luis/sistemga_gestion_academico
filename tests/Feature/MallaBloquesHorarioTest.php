<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\BloqueHorario;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Horario;
use App\Models\MallaCurricular;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 3 — Malla curricular, bloques de horario y horarios por aula:
 * límites de carga, clonado implícito por seeders, choques de aula/maestro,
 * protección de bloques en uso y autorización por permiso.
 */
class MallaBloquesHorarioTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $director;
    protected Modalidad $primaria;
    protected Grado $grado1ro;
    protected Aula $aula;
    protected AulaAsignaturaDocente $asignacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DatabaseSeeder']);

        $this->director = $this->crearUsuario('Director');
        $this->primaria = Modalidad::where('nombre', 'Primaria Regular')->firstOrFail();
        $this->grado1ro = Grado::where('nombre', '1ro')->where('modalidad_id', $this->primaria->id)->firstOrFail();

        $docente = Docente::create([
            'usuario_id' => Usuario::factory()->create()->id,
            'codigo_unico_persona' => 'MBH-0001',
            'sexo' => 'M',
        ]);

        // Apertura REAL vía servicio para que se clone la malla del grado.
        app(\App\Services\AulaService::class)->crearAulaConMalla([
            'nombre' => 'A',
            'grado_id' => $this->grado1ro->id,
            'modalidad_id' => $this->primaria->id,
            'turno' => 'Matutino',
            'docente_guia_id' => $docente->id,
            'anio_escolar_id' => \App\Models\AnioEscolar::where('nombre', '2026')->firstOrFail()->id,
            'cupo' => 30,
        ]);

        $this->aula = Aula::where('nombre', 'A')->firstOrFail();

        // Una asignación con docente y horas de malla (Matemática, 4h)
        $this->asignacion = AulaAsignaturaDocente::where('aula_id', $this->aula->id)
            ->where('asignatura_id', \App\Models\Asignatura::where('nombre', 'Matemática')->firstOrFail()->id)
            ->firstOrFail();
        $this->asignacion->update(['docente_id' => $docente->id]);
    }

    private function crearUsuario(string $rol, array $extra = []): Usuario
    {
        $usuario = Usuario::factory()->create($extra);
        $usuario->assignRole($rol);

        return $usuario;
    }

    private function crearDocente(): Docente
    {
        static $n = 0;
        $n++;

        return Docente::create([
            'usuario_id' => Usuario::factory()->create()->id,
            'codigo_unico_persona' => 'MBH-D' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'sexo' => 'F',
        ]);
    }

    // ==================================================================
    // MALLA CURRICULAR
    // ==================================================================

    public function test_solo_malla_gestionar_edita_la_malla(): void
    {
        // Director y Gestor tienen malla.gestionar
        foreach (['Director', 'Gestor de Usuarios'] as $rol) {
            $actor = $this->crearUsuario($rol);

            $this->actingAs($actor)
                ->get(route('academico.malla.index'))
                ->assertOk();
        }

        // El Docente Guía no la tiene: prohibido
        $docente = $this->crearUsuario('Docente Guia');
        $this->actingAs($docente)
            ->get(route('academico.malla.index'))
            ->assertForbidden();
    }

    public function test_agregar_asignatura_a_la_malla(): void
    {
        // La malla de 1ro ya suma 40h sobre el límite de 35: ampliamos el límite
        // para poder crear la nueva asignatura.
        $this->actingAs($this->director)
            ->put(route('academico.malla.grado.horas', $this->grado1ro), [
                'horas_maximas_semanales' => 60,
            ]);

        $quimica = \App\Models\Asignatura::where('nombre', 'Química')->firstOrFail();

        $this->actingAs($this->director)
            ->post(route('academico.malla.store'), [
                'grado_id' => $this->grado1ro->id,
                'asignatura_id' => $quimica->id,
                'horas_semanales_sugeridas' => 3,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('malla_curricular', [
            'grado_id' => $this->grado1ro->id,
            'asignatura_id' => $quimica->id,
        ]);
    }

    public function test_asignatura_duplicada_en_la_malla_se_rechaza(): void
    {
        $matematica = \App\Models\Asignatura::where('nombre', 'Matemática')->firstOrFail();

        $this->actingAs($this->director)
            ->post(route('academico.malla.store'), [
                'grado_id' => $this->grado1ro->id,
                'asignatura_id' => $matematica->id,
                'horas_semanales_sugeridas' => 4,
            ])
            ->assertSessionHas('error');
    }

    public function test_el_limite_de_horas_del_grado_no_puede_excederse(): void
    {
        // 11 materias de la malla de 1ro suman 40h; el límite por defecto es 35h
        // -> agregar cualquier materia más debe rechazarse.
        $quimica = \App\Models\Asignatura::where('nombre', 'Química')->firstOrFail();

        $this->actingAs($this->director)
            ->post(route('academico.malla.store'), [
                'grado_id' => $this->grado1ro->id,
                'asignatura_id' => $quimica->id,
                'horas_semanales_sugeridas' => 3,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('malla_curricular', [
            'grado_id' => $this->grado1ro->id,
            'asignatura_id' => $quimica->id,
        ]);
    }

    public function test_actualizar_horas_maximas_del_grado(): void
    {
        $this->actingAs($this->director)
            ->put(route('academico.malla.grado.horas', $this->grado1ro), [
                'horas_maximas_semanales' => 45,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('grado', ['id' => $this->grado1ro->id, 'horas_maximas_semanales' => 45]);
    }

    public function test_remover_asignatura_de_la_malla(): void
    {
        $item = MallaCurricular::where('grado_id', $this->grado1ro->id)
            ->whereHas('asignatura', fn ($q) => $q->where('nombre', 'Biblia'))->firstOrFail();

        $this->actingAs($this->director)
            ->delete(route('academico.malla.destroy', $item->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('malla_curricular', ['id' => $item->id]);
    }

    // ==================================================================
    // BLOQUES DE HORARIO
    // ==================================================================

    public function test_crear_bloque_con_autoincremento_de_numero(): void
    {
        // Los bloques seeded para primaria van de 1 a 4 (incluido recreo sin número).
        $this->actingAs($this->director)
            ->post(route('academico.bloques.store'), [
                'modalidad_id' => $this->primaria->id,
                'turno' => 'Matutino',
                'tipo_jornada' => 'Regular',
                'nombre' => '5ta Hora',
                'hora_inicio' => '09:45',
                'hora_fin' => '10:30',
            ])
            ->assertRedirect();

        $bloque = BloqueHorario::where('nombre', '5ta Hora')->firstOrFail();
        // Los bloques sembrados no tienen numero_bloque: el autoincremento arranca en 1.
        $this->assertSame(1, (int) $bloque->numero_bloque);
    }

    public function test_crear_bloque_de_recreo(): void
    {
        $this->actingAs($this->director)
            ->post(route('academico.bloques.store'), [
                'modalidad_id' => $this->primaria->id,
                'turno' => 'Matutino',
                'tipo_jornada' => 'Regular',
                'nombre' => 'Receso 2',
                'hora_inicio' => '10:30',
                'hora_fin' => '11:00',
                'es_recreo' => true,
            ])
            ->assertRedirect();

        $bloque = BloqueHorario::where('nombre', 'Receso 2')->firstOrFail();
        $this->assertTrue((bool) $bloque->es_recreo);
    }

    public function test_coordinador_no_puede_gestionar_bloques(): void
    {
        $coordinador = $this->crearUsuario('Coordinador');

        $this->actingAs($coordinador)
            ->post(route('academico.bloques.store'), [
                'modalidad_id' => $this->primaria->id,
                'turno' => 'Matutino',
                'tipo_jornada' => 'Regular',
                'nombre' => 'X',
                'hora_inicio' => '11:00',
                'hora_fin' => '11:45',
            ])
            ->assertForbidden();
    }

    public function test_no_se_puede_eliminar_un_bloque_en_uso(): void
    {
        $bloque = BloqueHorario::where('modalidad_id', $this->primaria->id)
            ->where('es_recreo', false)->firstOrFail();

        Horario::create([
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'dia_semana' => 'Lunes',
            'bloque_horario_id' => $bloque->id,
        ]);

        $this->actingAs($this->director)
            ->delete(route('academico.bloques.destroy', $bloque))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bloque_horario', ['id' => $bloque->id]);
    }

    public function test_eliminar_bloque_sin_uso(): void
    {
        $bloque = BloqueHorario::create([
            'modalidad_id' => $this->primaria->id,
            'turno' => 'Matutino',
            'tipo_jornada' => 'Regular',
            'numero_bloque' => 9,
            'nombre' => 'Bloque Libre',
            'hora_inicio' => '12:00',
            'hora_fin' => '12:45',
            'es_recreo' => false,
        ]);

        $this->actingAs($this->director)
            ->delete(route('academico.bloques.destroy', $bloque))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bloque_horario', ['id' => $bloque->id]);
    }

    // ==================================================================
    // HORARIOS POR AULA
    // ==================================================================

    public function test_asignar_clase_al_horario(): void
    {
        $bloque = BloqueHorario::where('modalidad_id', $this->primaria->id)
            ->where('es_recreo', false)->firstOrFail();

        $this->actingAs($this->director)
            ->post(route('academico.aulas.horarios.store', $this->aula), [
                'aula_asignatura_docente_id' => $this->asignacion->id,
                'dia_semana' => 'Lunes',
                'bloque_horario_id' => $bloque->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('horario', [
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'dia_semana' => 'Lunes',
            'bloque_horario_id' => $bloque->id,
        ]);
    }

    public function test_choque_de_aula_en_el_mismo_bloque(): void
    {
        $bloque = BloqueHorario::where('modalidad_id', $this->primaria->id)
            ->where('es_recreo', false)->firstOrFail();

        Horario::create([
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'dia_semana' => 'Martes',
            'bloque_horario_id' => $bloque->id,
        ]);

        // Otra materia del MISMA aula, mismo día/bloque: choque de aula
        $lengua = AulaAsignaturaDocente::where('aula_id', $this->aula->id)
            ->whereHas('asignatura', fn ($q) => $q->where('nombre', 'Lengua y Literatura'))
            ->firstOrFail();
        $lengua->update(['docente_id' => $this->crearDocente()->id]);

        $this->actingAs($this->director)
            ->post(route('academico.aulas.horarios.store', $this->aula), [
                'aula_asignatura_docente_id' => $lengua->id,
                'dia_semana' => 'Martes',
                'bloque_horario_id' => $bloque->id,
            ])
            ->assertSessionHas('error');
    }

    public function test_choque_de_maestro_en_otra_aula(): void
    {
        $bloque = BloqueHorario::where('modalidad_id', $this->primaria->id)
            ->where('es_recreo', false)->firstOrFail();

        Horario::create([
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'dia_semana' => 'Miercoles',
            'bloque_horario_id' => $bloque->id,
        ]);

        // La misma materia (mismo docente) en OTRA aula, mismo día/bloque
        app(\App\Services\AulaService::class)->crearAulaConMalla([
            'nombre' => 'B',
            'grado_id' => $this->grado1ro->id,
            'modalidad_id' => $this->primaria->id,
            'turno' => 'Matutino',
            'docente_guia_id' => $this->crearDocente()->id,
            'anio_escolar_id' => $this->aula->anio_escolar_id,
            'cupo' => 30,
        ]);
        $aulaB = Aula::where('nombre', 'B')->firstOrFail();
        $otraAsignacion = AulaAsignaturaDocente::where('aula_id', $aulaB->id)
            ->where('asignatura_id', $this->asignacion->asignatura_id)->firstOrFail();
        $otraAsignacion->update(['docente_id' => $this->asignacion->docente_id]);

        $this->actingAs($this->director)
            ->post(route('academico.aulas.horarios.store', $aulaB), [
                'aula_asignatura_docente_id' => $otraAsignacion->id,
                'dia_semana' => 'Miercoles',
                'bloque_horario_id' => $bloque->id,
            ])
            ->assertSessionHas('error');
    }

    public function test_no_se_puede_programar_en_un_recreo(): void
    {
        $recreo = BloqueHorario::where('modalidad_id', $this->primaria->id)
            ->where('es_recreo', true)->firstOrFail();

        $this->actingAs($this->director)
            ->post(route('academico.aulas.horarios.store', $this->aula), [
                'aula_asignatura_docente_id' => $this->asignacion->id,
                'dia_semana' => 'Jueves',
                'bloque_horario_id' => $recreo->id,
            ])
            ->assertSessionHas('error');
    }

    public function test_materia_sin_docente_no_puede_programarse(): void
    {
        $bloque = BloqueHorario::where('modalidad_id', $this->primaria->id)
            ->where('es_recreo', false)->firstOrFail();

        // Materia de la malla clonada sin docente asignado
        $sinDocente = AulaAsignaturaDocente::where('aula_id', $this->aula->id)
            ->whereNull('docente_id')->firstOrFail();

        $this->actingAs($this->director)
            ->post(route('academico.aulas.horarios.store', $this->aula), [
                'aula_asignatura_docente_id' => $sinDocente->id,
                'dia_semana' => 'Viernes',
                'bloque_horario_id' => $bloque->id,
            ])
            ->assertSessionHas('error');
    }

    public function test_docente_guia_puede_gestionar_horarios_pero_el_docente_de_materia_no(): void
    {
        $bloque = BloqueHorario::where('modalidad_id', $this->primaria->id)
            ->where('es_recreo', false)->firstOrFail();

        $payload = [
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'dia_semana' => 'Viernes',
            'bloque_horario_id' => $bloque->id,
        ];

        // Docente Guía tiene horarios.gestionar
        $guia = $this->crearUsuario('Docente Guia');
        $this->actingAs($guia)
            ->post(route('academico.aulas.horarios.store', $this->aula), $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        // Docente por Asignatura solo tiene horarios.ver
        $materia = $this->crearUsuario('Docente por Asignatura');
        $this->actingAs($materia)
            ->post(route('academico.aulas.horarios.store', $this->aula), $payload)
            ->assertForbidden();
    }

    public function test_eliminar_bloque_de_horario(): void
    {
        $bloque = BloqueHorario::where('modalidad_id', $this->primaria->id)
            ->where('es_recreo', false)->firstOrFail();
        $horario = Horario::create([
            'aula_asignatura_docente_id' => $this->asignacion->id,
            'dia_semana' => 'Jueves',
            'bloque_horario_id' => $bloque->id,
        ]);

        $this->actingAs($this->director)
            ->delete(route('academico.aulas.horarios.destroy', [$this->aula, $horario]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('horario', ['id' => $horario->id]);
    }
}
