<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Horario;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 3 — Aulas y Asignaciones: apertura con clonación de malla,
 * reglas anti-duplicados, jerarquía de coordinadores y asignación masiva.
 */
class AulaModuleTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $director;
    protected Modalidad $primaria;
    protected AnioEscolar $anio;
    protected Grado $grado1ro;
    protected Docente $docenteGuia;

    protected function setUp(): void
    {
        parent::setUp();

        // Catálogos completos del colegio (malla, grados, asignaturas, cortes...)
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DatabaseSeeder']);

        $this->director = $this->crearUsuario('Director');
        $this->primaria = Modalidad::where('nombre', 'Primaria Regular')->firstOrFail();
        $this->anio = AnioEscolar::where('nombre', '2026')->firstOrFail();
        $this->grado1ro = Grado::where('nombre', '1ro')->where('modalidad_id', $this->primaria->id)->firstOrFail();
        $this->docenteGuia = Docente::create([
            'usuario_id' => Usuario::factory()->create()->id,
            'codigo_unico_persona' => 'FASE3-0001',
            'sexo' => 'M',
        ]);
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
            'codigo_unico_persona' => 'FASE3-D' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'sexo' => 'F',
        ]);
    }

    private function payloadAula(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'B',
            'grado_id' => $this->grado1ro->id,
            'modalidad_id' => $this->primaria->id,
            'turno' => 'Matutino',
            'docente_guia_id' => $this->docenteGuia->id,
            'anio_escolar_id' => $this->anio->id,
            'cupo' => 30,
        ], $extra);
    }

    /**
     * Apertura REAL de aula (vía servicio): clona la malla del grado,
     * igual que hace la interfaz del sistema.
     */
    private function aperturarAula(array $extra = []): Aula
    {
        app(\App\Services\AulaService::class)->crearAulaConMalla($this->payloadAula($extra));

        return Aula::where('nombre', $this->payloadAula($extra)['nombre'])
            ->where('anio_escolar_id', $this->anio->id)
            ->firstOrFail();
    }

    // ------------------------------------------------------------------
    // Autorización
    // ------------------------------------------------------------------

    public function test_direccion_gestor_y_docentes_pueden_ver_aulas(): void
    {
        foreach (['Director', 'Subdirector', 'Gestor de Usuarios', 'Docente Guia', 'Coordinador'] as $rol) {
            $actor = $this->crearUsuario($rol);

            $this->actingAs($actor)
                ->get(route('academico.aulas.index'))
                ->assertOk();
        }
    }

    public function test_secretaria_no_puede_ver_aulas(): void
    {
        $secretaria = $this->crearUsuario('Secretaria');

        $this->actingAs($secretaria)
            ->get(route('academico.aulas.index'))
            ->assertForbidden();
    }

    public function test_docente_guia_no_puede_aperturar_aulas(): void
    {
        $docente = $this->crearUsuario('Docente Guia');

        $this->actingAs($docente)
            ->post(route('academico.aulas.store'), $this->payloadAula())
            ->assertForbidden();

        $this->assertSame(0, Aula::count());
    }

    // ------------------------------------------------------------------
    // Apertura de aulas (store) con clonación de malla
    // ------------------------------------------------------------------

    public function test_gestor_apertura_aula_y_la_malla_se_clona(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');

        $this->actingAs($gestor)
            ->post(route('academico.aulas.store'), $this->payloadAula())
            ->assertRedirect(route('academico.aulas.index'))
            ->assertSessionHas('success');

        $aula = Aula::where('nombre', 'B')->where('anio_escolar_id', $this->anio->id)->firstOrFail();
        $this->assertSame($this->grado1ro->id, $aula->grado_id);
        $this->assertSame(11, AulaAsignaturaDocente::where('aula_id', $aula->id)->count());
        $this->assertNull(AulaAsignaturaDocente::where('aula_id', $aula->id)->first()->docente_id);
    }

    public function test_anti_duplicados_bloquea_la_misma_combinacion(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');

        $this->actingAs($gestor)->post(route('academico.aulas.store'), $this->payloadAula());

        $otroDocente = $this->crearDocente();

        // Misma combinación año + grado + sección (cambia solo el docente guía):
        // StoreAulaRequest la bloquea con la regla unique de 'nombre'.
        $this->actingAs($gestor)
            ->post(route('academico.aulas.store'), $this->payloadAula([
                'docente_guia_id' => $otroDocente->id,
            ]))
            ->assertSessionHasErrors('nombre');

        $this->assertSame(1, Aula::where('nombre', 'B')->where('anio_escolar_id', $this->anio->id)->count());
    }

    public function test_un_docente_guia_no_puede_guiar_dos_aulas_el_mismo_anio(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');

        $this->actingAs($gestor)->post(route('academico.aulas.store'), $this->payloadAula());

        // Misma aula-distinta sección en otro grado: choca por docente_guia_id único por año.
        $grado2do = Grado::where('nombre', '2do')->where('modalidad_id', $this->primaria->id)->firstOrFail();

        $this->actingAs($gestor)
            ->post(route('academico.aulas.store'), $this->payloadAula(['grado_id' => $grado2do->id]))
            ->assertSessionHasErrors('docente_guia_id');
    }

    // ------------------------------------------------------------------
    // Actualización y re-sincronización de malla
    // ------------------------------------------------------------------

    public function test_actualizar_aula_cambia_cupo(): void
    {
        $aula = Aula::create($this->payloadAula());
        $aula->update(['nombre' => 'B']);

        $this->actingAs($this->director)
            ->put(route('academico.aulas.update', $aula), $this->payloadAula([
                'nombre' => 'B',
                'cupo' => 40,
            ]))
            ->assertRedirect(route('academico.aulas.index'));

        $this->assertDatabaseHas('aula', ['id' => $aula->id, 'cupo' => 40]);
    }

    public function test_cambiar_el_grado_re_sincroniza_la_malla(): void
    {
        $aula = $this->aperturarAula();
        $this->assertSame(11, AulaAsignaturaDocente::where('aula_id', $aula->id)->count());

        // Mover el aula a 2do grado: su malla tiene las mismas 11 materias,
        // pero la re-sincronización debe reemplazar las asignaciones.
        $grado2do = Grado::where('nombre', '2do')->where('modalidad_id', $this->primaria->id)->firstOrFail();

        $this->actingAs($this->director)
            ->put(route('academico.aulas.update', $aula), $this->payloadAula([
                'grado_id' => $grado2do->id,
            ]))
            ->assertRedirect(route('academico.aulas.index'));

        $aula->refresh();
        $this->assertSame($grado2do->id, $aula->grado_id);
        $this->assertSame(11, AulaAsignaturaDocente::where('aula_id', $aula->id)->count());
    }

    public function test_coordinador_solo_gestiona_aulas_de_su_modalidad(): void
    {
        $secundaria = Modalidad::where('nombre', 'Secundaria Regular')->firstOrFail();
        $coordinador = $this->crearUsuario('Coordinador');
        Docente::create([
            'usuario_id' => $coordinador->id,
            'codigo_unico_persona' => 'FASE3-COORD',
            'sexo' => 'F',
            'es_coordinador' => true,
            'modalidad_coordina_id' => $this->primaria->id,
        ]);

        $aulaPrimaria = Aula::create($this->payloadAula());
        $aulaSecundaria = Aula::create($this->payloadAula([
            'nombre' => '7A',
            'grado_id' => Grado::where('nombre', '7mo')->where('modalidad_id', $secundaria->id)->firstOrFail()->id,
            'modalidad_id' => $secundaria->id,
        ]));

        // Su modalidad: permitido
        $this->actingAs($coordinador)
            ->put(route('academico.aulas.update', $aulaPrimaria), $this->payloadAula(['cupo' => 33]))
            ->assertRedirect(route('academico.aulas.index'));

        // Otra modalidad: prohibido
        $this->actingAs($coordinador)
            ->put(route('academico.aulas.update', $aulaSecundaria), $this->payloadAula([
                'cupo' => 44,
                'grado_id' => Grado::where('nombre', '7mo')->where('modalidad_id', $secundaria->id)->firstOrFail()->id,
                'modalidad_id' => $secundaria->id,
            ]))
            ->assertForbidden();
    }

    public function test_eliminar_aula_borra_sus_asignaciones(): void
    {
        $aula = $this->aperturarAula();
        $this->assertGreaterThan(0, AulaAsignaturaDocente::where('aula_id', $aula->id)->count());

        $this->actingAs($this->director)
            ->delete(route('academico.aulas.destroy', $aula))
            ->assertRedirect(route('academico.aulas.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('aula', ['id' => $aula->id]);
        $this->assertSame(0, AulaAsignaturaDocente::where('aula_id', $aula->id)->count());
    }

    // ------------------------------------------------------------------
    // Asignaciones: materia extra, docente y masivo
    // ------------------------------------------------------------------

    public function test_asignar_materia_extra_a_un_aula(): void
    {
        $aula = Aula::create($this->payloadAula());
        $quimica = Asignatura::where('nombre', 'Química')->firstOrFail();

        $this->actingAs($this->director)
            ->post(route('academico.aulas.asignaturas.store', $aula), [
                'asignatura_id' => $quimica->id,
                'horas_semanales' => 3,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('aula_asignatura_docente', [
            'aula_id' => $aula->id,
            'asignatura_id' => $quimica->id,
            'horas_semanales' => 3,
        ]);

        // Duplicada: rechazada
        $this->actingAs($this->director)
            ->post(route('academico.aulas.asignaturas.store', $aula), [
                'asignatura_id' => $quimica->id,
                'horas_semanales' => 3,
            ])
            ->assertSessionHas('error');
    }

    public function test_asignacion_masiva_asigna_docente_a_varias_aulas(): void
    {
        $aulaA = Aula::create($this->payloadAula(['nombre' => 'A']));
        $aulaB = Aula::create($this->payloadAula(['nombre' => 'B', 'docente_guia_id' => $this->crearDocente()->id]));
        $matematica = Asignatura::where('nombre', 'Matemática')->firstOrFail();
        $docente = $this->crearDocente();

        $this->actingAs($this->director)
            ->post(route('academico.asignaciones.masivo'), [
                'asignatura_id' => $matematica->id,
                'docente_id' => $docente->id,
                'aulas_ids' => [$aulaA->id, $aulaB->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach ([$aulaA, $aulaB] as $aula) {
            $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)
                ->where('asignatura_id', $matematica->id)->firstOrFail();
            $this->assertSame($docente->id, $asignacion->docente_id);
        }
    }

    public function test_asignacion_masiva_requiere_al_menos_un_aula(): void
    {
        $matematica = Asignatura::where('nombre', 'Matemática')->firstOrFail();

        $this->actingAs($this->director)
            ->post(route('academico.asignaciones.masivo'), [
                'asignatura_id' => $matematica->id,
                'docente_id' => $this->crearDocente()->id,
                'aulas_ids' => [],
            ])
            ->assertSessionHasErrors('aulas_ids');
    }

    public function test_cambiar_docente_de_materia_con_choque_de_horario_se_bloquea(): void
    {
        $aula = $this->aperturarAula(['nombre' => 'A']);
        $matematica = Asignatura::where('nombre', 'Matemática')->firstOrFail();
        $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)
            ->where('asignatura_id', $matematica->id)->firstOrFail();

        $docenteLibre = $this->crearDocente();
        $docenteOcupado = $this->crearDocente();

        // Asignar horario a la materia
        $asignacion->update(['docente_id' => $docenteLibre->id]);
        $bloque = \App\Models\BloqueHorario::where('modalidad_id', $this->primaria->id)->firstOrFail();
        Horario::create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'dia_semana' => 'Lunes',
            'bloque_horario_id' => $bloque->id,
        ]);

        // El docente ocupado ya imparte ese mismo día/bloque en OTRA aula
        $aulaOtra = $this->aperturarAula(['nombre' => 'Z', 'docente_guia_id' => $this->crearDocente()->id]);
        $asignacionOtra = AulaAsignaturaDocente::where('aula_id', $aulaOtra->id)
            ->where('asignatura_id', $matematica->id)->firstOrFail();
        $asignacionOtra->update(['docente_id' => $docenteOcupado->id]);
        Horario::create([
            'aula_asignatura_docente_id' => $asignacionOtra->id,
            'dia_semana' => 'Lunes',
            'bloque_horario_id' => $bloque->id,
        ]);

        // Intentar asignar al docente ocupado a la primera materia: bloqueado por choque
        $this->actingAs($this->director)
            ->put(route('academico.aulas.asignaturas.update', [$aula, $asignacion]), [
                'docente_id' => $docenteOcupado->id,
            ])
            ->assertSessionHas('error');

        $this->assertSame($docenteLibre->id, $asignacion->fresh()->docente_id);
    }
}
