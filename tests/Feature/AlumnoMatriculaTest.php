<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 4 — Alumnos, matrículas y prematrícula: expediente, bloqueo de doble
 * matrícula, retiro/reactivación con su jerarquía, y promoción de año.
 */
class AlumnoMatriculaTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $director;
    protected Usuario $secretaria;
    protected Modalidad $primaria;
    protected AnioEscolar $anio2026;
    protected Aula $aula1roA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DatabaseSeeder']);

        $this->director = $this->crearUsuario('Director');
        $this->secretaria = $this->crearUsuario('Secretaria');
        $this->primaria = Modalidad::where('nombre', 'Primaria Regular')->firstOrFail();
        $this->anio2026 = AnioEscolar::where('nombre', '2026')->firstOrFail();

        $this->aula1roA = $this->aperturarAula('1ro', 'A', $this->anio2026);
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
            'codigo_unico_persona' => 'AM-D' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'sexo' => 'F',
        ]);
    }

    private function aperturarAula(string $gradoNombre, string $seccion, AnioEscolar $anio): Aula
    {
        $grado = Grado::where('nombre', $gradoNombre)->where('modalidad_id', $this->primaria->id)->firstOrFail();

        app(\App\Services\AulaService::class)->crearAulaConMalla([
            'nombre' => $seccion,
            'grado_id' => $grado->id,
            'modalidad_id' => $this->primaria->id,
            'turno' => 'Matutino',
            'docente_guia_id' => $this->crearDocente()->id,
            'anio_escolar_id' => $anio->id,
            'cupo' => 30,
        ]);

        return Aula::where('grado_id', $grado->id)
            ->where('nombre', $seccion)
            ->where('anio_escolar_id', $anio->id)
            ->firstOrFail();
    }

    private function payloadAlumno(array $extra = []): array
    {
        static $n = 0;
        $n++;

        return array_merge([
            'codigo_unico_persona' => 'AM-A' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'nombre_completo' => 'Alumno de Prueba ' . $n,
            'sexo' => 'M',
            'fecha_nacimiento' => '2019-04-12',
            'direccion_domiciliar' => 'Barrio Prueba, Managua',
            'acepta_compromiso_cristiano' => true,
        ], $extra);
    }

    private function matricularAlumnoEn(Alumno $alumno, Aula $aula, ?AnioEscolar $anio = null): Matricula
    {
        return Matricula::create([
            'alumno_id' => $alumno->id,
            'aula_id' => $aula->id,
            'anio_escolar_id' => $anio?->id ?? $aula->anio_escolar_id,
            'estado' => 'activo',
            'fecha_matricula' => now()->toDateString(),
        ]);
    }

    // ==================================================================
    // ALUMNOS (expediente)
    // ==================================================================

    public function test_roles_del_colegio_pueden_ver_alumnos(): void
    {
        foreach (['Director', 'Gestor de Usuarios', 'Secretaria', 'Docente Guia', 'Docente por Asignatura', 'Coordinador'] as $rol) {
            $actor = $this->crearUsuario($rol);

            $this->actingAs($actor)
                ->get(route('academico.alumnos.index'))
                ->assertOk();
        }
    }

    public function test_rol_alumno_no_puede_ver_el_listado(): void
    {
        $alumnoUser = $this->crearUsuario('Alumno');

        $this->actingAs($alumnoUser)
            ->get(route('academico.alumnos.index'))
            ->assertForbidden();
    }

    public function test_solo_direccion_registra_alumnos(): void
    {
        // La policy exige rol Director/Subdirector (más estricto que alumnos.gestionar)
        $this->actingAs($this->director)
            ->post(route('academico.alumnos.store'), $this->payloadAlumno())
            ->assertRedirect(route('academico.alumnos.index'));

        $this->assertDatabaseCount('alumno', 1);
    }

    public function test_secretaria_y_gestor_no_registran_alumnos(): void
    {
        foreach ([$this->secretaria, $this->crearUsuario('Gestor de Usuarios')] as $actor) {
            $this->actingAs($actor)
                ->post(route('academico.alumnos.store'), $this->payloadAlumno())
                ->assertForbidden();
        }

        $this->assertDatabaseCount('alumno', 0);
    }

    public function test_registro_valida_campos_y_codigo_unico(): void
    {
        $payload = $this->payloadAlumno();

        $this->actingAs($this->director)
            ->post(route('academico.alumnos.store'), $payload)
            ->assertRedirect();

        // Mismo código: rechazado por la regla unique
        $this->actingAs($this->director)
            ->post(route('academico.alumnos.store'), array_merge($payload, [
                'nombre_completo' => 'Otro Alumno',
            ]))
            ->assertSessionHasErrors('codigo_unico_persona');

        $this->assertSame(1, \App\Models\Alumno::where('codigo_unico_persona', $payload['codigo_unico_persona'])->count());
    }

    public function test_registro_sin_datos_exige_los_campos_obligatorios(): void
    {
        $respuesta = $this->actingAs($this->director)
            ->post(route('academico.alumnos.store'), []);

        $respuesta->assertStatus(302);
        $respuesta->assertSessionHasErrors([
            'codigo_unico_persona', 'nombre_completo', 'sexo', 'fecha_nacimiento', 'direccion_domiciliar',
        ]);
    }

    public function test_editar_alumno(): void
    {
        $alumno = Alumno::create($this->payloadAlumno());

        $this->actingAs($this->director)
            ->put(route('academico.alumnos.update', $alumno), $this->payloadAlumno([
                'nombre_completo' => 'Nombre Editado',
            ]))
            ->assertRedirect(route('academico.alumnos.index'));

        $this->assertDatabaseHas('alumno', ['id' => $alumno->id, 'nombre_completo' => 'Nombre Editado']);
    }

    public function test_eliminar_alumno_es_borrado_logico(): void
    {
        $alumno = Alumno::create($this->payloadAlumno());
        $this->matricularAlumnoEn($alumno, $this->aula1roA);

        $this->actingAs($this->director)
            ->delete(route('academico.alumnos.destroy', $alumno))
            ->assertRedirect(route('academico.alumnos.index'));

        $this->assertSoftDeleted('alumno', ['id' => $alumno->id]);
    }

    public function test_secretaria_no_elimina_alumnos(): void
    {
        $alumno = Alumno::create($this->payloadAlumno());

        $this->actingAs($this->secretaria)
            ->delete(route('academico.alumnos.destroy', $alumno))
            ->assertForbidden();

        $this->assertDatabaseHas('alumno', ['id' => $alumno->id]);
    }

    // ==================================================================
    // MATRÍCULAS
    // ==================================================================

    public function test_roles_con_alumnos_ver_pueden_listar_matriculas(): void
    {
        foreach (['Director', 'Secretaria', 'Gestor de Usuarios', 'Docente Guia'] as $rol) {
            $actor = $this->crearUsuario($rol);

            $this->actingAs($actor)
                ->get(route('academico.matriculas.index'))
                ->assertOk();
        }
    }

    public function test_rol_alumno_no_puede_listar_matriculas(): void
    {
        $alumnoUser = $this->crearUsuario('Alumno');

        $this->actingAs($alumnoUser)
            ->get(route('academico.matriculas.index'))
            ->assertForbidden();
    }

    public function test_procesar_matricula(): void
    {
        $alumno = Alumno::create($this->payloadAlumno());

        // UX de la rama feauture/no: redirige al formulario de creación para agilizar matrículas masivas.
        $this->actingAs($this->secretaria)
            ->post(route('academico.matriculas.store'), [
                'alumno_id' => $alumno->id,
                'aula_id' => $this->aula1roA->id,
                'anio_escolar_id' => $this->anio2026->id,
                'estado' => 'activo',
                'fecha_matricula' => now()->toDateString(),
            ])
            ->assertRedirect(route('academico.matriculas.create'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('matricula', [
            'alumno_id' => $alumno->id,
            'aula_id' => $this->aula1roA->id,
            'estado' => 'activo',
        ]);
    }

    public function test_doble_matricula_en_el_mismo_anio_se_bloquea(): void
    {
        $alumno = Alumno::create($this->payloadAlumno());
        $this->matricularAlumnoEn($alumno, $this->aula1roA);

        $aula1roB = $this->aperturarAula('1ro', 'B', $this->anio2026);

        $this->actingAs($this->secretaria)
            ->post(route('academico.matriculas.store'), [
                'alumno_id' => $alumno->id,
                'aula_id' => $aula1roB->id,
                'anio_escolar_id' => $this->anio2026->id,
                'estado' => 'activo',
                'fecha_matricula' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('anio_escolar_id');

        $this->assertSame(1, Matricula::where('alumno_id', $alumno->id)->count());
    }

    public function test_el_formulario_excluye_alumnos_ya_matriculados_en_el_anio(): void
    {
        $matriculado = Alumno::create($this->payloadAlumno());
        $nuevo = Alumno::create($this->payloadAlumno());
        $this->matricularAlumnoEn($matriculado, $this->aula1roA);

        $this->actingAs($this->secretaria)
            ->get(route('academico.matriculas.create'))
            ->assertOk()
            ->assertSee($nuevo->nombre_completo)
            ->assertDontSee($matriculado->nombre_completo);
    }

    public function test_retirar_y_reactivar_matricula(): void
    {
        $alumno = Alumno::create($this->payloadAlumno());
        $matricula = $this->matricularAlumnoEn($alumno, $this->aula1roA);

        // La Secretaria puede matricular pero NO retirar (update exige jerarquía)
        $this->actingAs($this->secretaria)
            ->patch(route('academico.matriculas.retirar', $matricula))
            ->assertForbidden();

        // El Director sí
        $this->actingAs($this->director)
            ->patch(route('academico.matriculas.retirar', $matricula))
            ->assertRedirect(route('academico.matriculas.index'));

        $this->assertDatabaseHas('matricula', ['id' => $matricula->id, 'estado' => 'retirado']);

        $this->actingAs($this->director)
            ->patch(route('academico.matriculas.reactivar', $matricula))
            ->assertRedirect(route('academico.matriculas.index'));

        $this->assertDatabaseHas('matricula', ['id' => $matricula->id, 'estado' => 'activo']);
    }

    public function test_eliminar_matricula_es_borrado_logico(): void
    {
        $alumno = Alumno::create($this->payloadAlumno());
        $matricula = $this->matricularAlumnoEn($alumno, $this->aula1roA);

        $this->actingAs($this->director)
            ->delete(route('academico.matriculas.destroy', $matricula))
            ->assertRedirect(route('academico.matriculas.index'));

        $this->assertSoftDeleted('matricula', ['id' => $matricula->id]);
    }

    // ==================================================================
    // PREMATRÍCULA (promoción de año)
    // ==================================================================

    private function prepararPromocion(): Matricula
    {
        $alumno = Alumno::create($this->payloadAlumno());
        $matricula = $this->matricularAlumnoEn($alumno, $this->aula1roA);

        // Próximo año (2027, del seeder) + aula destino "2do - A"
        $anio2027 = AnioEscolar::where('nombre', '2027')->firstOrFail();
        $this->aperturarAula('2do', 'A', $anio2027);

        return $matricula;
    }

    public function test_promover_alumno_al_proximo_grado_y_anio(): void
    {
        $matricula = $this->prepararPromocion();
        $guia = $this->crearUsuario('Docente Guia');
        Docente::create(['usuario_id' => $guia->id, 'codigo_unico_persona' => 'AM-G1', 'sexo' => 'M']);

        $this->actingAs($guia)
            ->post(route('academico.prematricula.promover', $matricula))
            ->assertRedirect()
            ->assertSessionHas('success');

        $anio2027 = AnioEscolar::where('nombre', '2027')->firstOrFail();
        $this->assertDatabaseHas('matricula', [
            'alumno_id' => $matricula->alumno_id,
            'anio_escolar_id' => $anio2027->id,
            'estado' => 'activo',
        ]);
    }

    public function test_no_promueve_alumnos_en_reparacion(): void
    {
        $matricula = $this->prepararPromocion();
        $guia = $this->crearUsuario('Docente Guia');
        Docente::create(['usuario_id' => $guia->id, 'codigo_unico_persona' => 'AM-G2', 'sexo' => 'M']);

        $this->actingAs($guia)
            ->post(route('academico.prematricula.promover', $matricula), [
                'estado_academico' => 'En Reparación',
            ])
            ->assertSessionHas('error');

        $anio2027 = AnioEscolar::where('nombre', '2027')->firstOrFail();
        $this->assertDatabaseMissing('matricula', [
            'alumno_id' => $matricula->alumno_id,
            'anio_escolar_id' => $anio2027->id,
        ]);
    }

    public function test_promocion_falla_si_el_aula_destino_no_existe(): void
    {
        $alumno = Alumno::create($this->payloadAlumno());
        $matricula = $this->matricularAlumnoEn($alumno, $this->aula1roA);

        $guia = $this->crearUsuario('Docente Guia');
        Docente::create(['usuario_id' => $guia->id, 'codigo_unico_persona' => 'AM-G3', 'sexo' => 'M']);

        // Sin aula "2do - A" para 2027
        $this->actingAs($guia)
            ->post(route('academico.prematricula.promover', $matricula))
            ->assertSessionHas('error');

        $anio2027 = AnioEscolar::where('nombre', '2027')->firstOrFail();
        $this->assertDatabaseMissing('matricula', [
            'alumno_id' => $matricula->alumno_id,
            'anio_escolar_id' => $anio2027->id,
        ]);
    }

    public function test_remitir_expediente(): void
    {
        $matricula = $this->prepararPromocion();
        $guia = $this->crearUsuario('Docente Guia');
        Docente::create(['usuario_id' => $guia->id, 'codigo_unico_persona' => 'AM-G4', 'sexo' => 'M']);

        $this->actingAs($guia)
            ->post(route('academico.prematricula.remitir', $matricula))
            ->assertRedirect()
            ->assertSessionHas('success');
    }
}
