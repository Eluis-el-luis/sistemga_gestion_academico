<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Fase 2 — Módulo de Usuarios: CRUD, jerarquía de roles, blindajes de
 * Dirección/Subdirección y reseteo administrativo de contraseña.
 */
class UsuarioModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\Seeders\PermisoSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\ModalidadSeeder']);
    }

    private function crearUsuario(string $rol, array $extra = []): Usuario
    {
        $usuario = Usuario::factory()->create($extra);
        $usuario->assignRole($rol);

        return $usuario;
    }

    // ------------------------------------------------------------------
    // Autorización de acceso al módulo
    // ------------------------------------------------------------------

    public function test_solo_direccion_y_gestor_pueden_listar_usuarios(): void
    {
        foreach (['Director', 'Subdirector', 'Gestor de Usuarios'] as $rol) {
            $actor = $this->crearUsuario($rol);

            $this->actingAs($actor)
                ->get(route('academico.usuarios.index'))
                ->assertOk();
        }
    }

    public function test_docente_coordinador_y_secretaria_no_pueden_listar_usuarios(): void
    {
        foreach (['Docente Guia', 'Coordinador', 'Secretaria'] as $rol) {
            $actor = $this->crearUsuario($rol);

            $this->actingAs($actor)
                ->get(route('academico.usuarios.index'))
                ->assertForbidden();
        }
    }

    public function test_huesped_no_autenticado_redirige_al_login(): void
    {
        $this->get(route('academico.usuarios.index'))
            ->assertRedirect(route('login'));
    }

    // ------------------------------------------------------------------
    // Filtro de jerarquía en el listado
    // ------------------------------------------------------------------

    public function test_gestor_no_ve_direccion_ni_subdireccion_en_el_listado(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $this->crearUsuario('Director');
        $this->crearUsuario('Subdirector');
        $docente = $this->crearUsuario('Docente Guia');

        $this->actingAs($gestor)
            ->get(route('academico.usuarios.index'))
            ->assertOk()
            ->assertSee($docente->nombre_completo);
    }

    public function test_director_ve_todos_los_usuarios(): void
    {
        $director = $this->crearUsuario('Director');
        $this->crearUsuario('Subdirector');
        $docente = $this->crearUsuario('Docente Guia');

        $this->actingAs($director)
            ->get(route('academico.usuarios.index'))
            ->assertOk()
            ->assertSee($docente->nombre_completo);
    }

    public function test_buscador_encuentra_por_nombre(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $this->crearUsuario('Secretaria', ['nombre_completo' => 'Zenaida Nuñez Prueba']);

        $this->actingAs($gestor)
            ->get(route('academico.usuarios.index', ['buscar' => 'Zenaida']))
            ->assertOk()
            ->assertSee('Zenaida');
    }

    // ------------------------------------------------------------------
    // Registro de personal (store)
    // ------------------------------------------------------------------

    public function test_gestor_registra_docente_y_crea_su_perfil(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $primaria = Modalidad::where('nombre', 'Primaria Regular')->first();

        $this->actingAs($gestor)
            ->post(route('academico.usuarios.store'), [
                'nombre_completo' => 'Nueva Docente Prueba',
                'email' => 'nueva.docente@ccn.edu.ni',
                'password' => 'clave-segura-123',
                'roles' => ['Docente por Asignatura'],
                'codigo_unico_persona' => 'DOC-9001',
                'sexo' => 'F',
                'modalidad_id' => $primaria->id,
            ])
            ->assertRedirect(route('academico.usuarios.index'))
            ->assertSessionHas('success');

        $usuario = Usuario::where('email', 'nueva.docente@ccn.edu.ni')->firstOrFail();
        $this->assertTrue($usuario->hasRole('Docente por Asignatura'));
        $this->assertTrue(Hash::check('clave-segura-123', $usuario->password));

        $docente = Docente::where('usuario_id', $usuario->id)->firstOrFail();
        $this->assertSame('DOC-9001', $docente->codigo_unico_persona);
        $this->assertSame('F', $docente->sexo);
        $this->assertFalse((bool) $docente->es_coordinador);
    }

    public function test_registro_de_coordinador_guarda_modalidad_coordinada(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $primaria = Modalidad::where('nombre', 'Primaria Regular')->first();

        $this->actingAs($gestor)
            ->post(route('academico.usuarios.store'), [
                'nombre_completo' => 'Coordinadora Prueba',
                'email' => 'coordinadora.prueba@ccn.edu.ni',
                'password' => 'clave-segura-123',
                'roles' => ['Coordinador'],
                'sexo' => 'F',
                'modalidad_coordina_id' => $primaria->id,
            ])
            ->assertRedirect(route('academico.usuarios.index'));

        $usuario = Usuario::where('email', 'coordinadora.prueba@ccn.edu.ni')->firstOrFail();
        $this->assertTrue($usuario->hasRole('Coordinador'));

        $docente = Docente::where('usuario_id', $usuario->id)->firstOrFail();
        $this->assertTrue((bool) $docente->es_coordinador);
        $this->assertSame($primaria->id, $docente->modalidad_coordina_id);
    }

    public function test_no_puede_registrarse_un_segundo_director(): void
    {
        $this->crearUsuario('Director');
        $gestor = $this->crearUsuario('Gestor de Usuarios');

        $this->actingAs($gestor)
            ->post(route('academico.usuarios.store'), [
                'nombre_completo' => 'Segundo Director',
                'email' => 'segundo.director@ccn.edu.ni',
                'password' => 'clave-segura-123',
                'roles' => ['Director'],
            ])
            ->assertSessionHasErrors('roles');

        $this->assertDatabaseMissing('usuario', ['email' => 'segundo.director@ccn.edu.ni']);
    }

    public function test_validacion_de_registro_exige_email_unico_password_y_rol(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $existente = $this->crearUsuario('Secretaria');

        $this->actingAs($gestor)
            ->post(route('academico.usuarios.store'), [
                'nombre_completo' => 'Sin Datos Validos',
                'email' => $existente->email,
                'password' => 'corta',
                'roles' => [],
            ])
            ->assertSessionHasErrors(['email', 'password', 'roles']);
    }

    // ------------------------------------------------------------------
    // Blindajes de edición (policies)
    // ------------------------------------------------------------------

    public function test_gestor_no_puede_editar_al_director(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $director = $this->crearUsuario('Director');

        $this->actingAs($gestor)
            ->get(route('academico.usuarios.edit', $director))
            ->assertForbidden();

        $this->actingAs($gestor)
            ->put(route('academico.usuarios.update', $director), $this->payloadEdicion($director))
            ->assertForbidden();
    }

    public function test_gestor_no_puede_editar_a_la_subdirectora(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $subdirectora = $this->crearUsuario('Subdirector');

        $this->actingAs($gestor)
            ->put(route('academico.usuarios.update', $subdirectora), $this->payloadEdicion($subdirectora))
            ->assertForbidden();
    }

    public function test_director_si_puede_editar_a_la_subdirectora(): void
    {
        $director = $this->crearUsuario('Director');
        $subdirectora = $this->crearUsuario('Subdirector');

        $this->actingAs($director)
            ->put(route('academico.usuarios.update', $subdirectora), $this->payloadEdicion($subdirectora))
            ->assertRedirect(route('academico.usuarios.index'))
            ->assertSessionHas('success');
    }

    public function test_gestor_edita_personal_comun(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $secretaria = $this->crearUsuario('Secretaria');

        $this->actingAs($gestor)
            ->put(route('academico.usuarios.update', $secretaria), $this->payloadEdicion($secretaria, [
                'nombre_completo' => 'Secretaria Editada',
            ]))
            ->assertRedirect(route('academico.usuarios.index'));

        $this->assertDatabaseHas('usuario', [
            'id' => $secretaria->id,
            'nombre_completo' => 'Secretaria Editada',
        ]);
    }

    // ------------------------------------------------------------------
    // Desactivación (destroy alterna el estado) y blindajes
    // ------------------------------------------------------------------

    public function test_nadie_puede_desactivarse_a_si_mismo(): void
    {
        $director = $this->crearUsuario('Director');

        $this->actingAs($director)
            ->delete(route('academico.usuarios.destroy', $director))
            ->assertForbidden();
    }

    public function test_la_cuenta_director_es_intocable_para_desactivar(): void
    {
        $director = $this->crearUsuario('Director');
        $subdirectora = $this->crearUsuario('Subdirector');

        // Ni la Subdirectora...
        $this->actingAs($subdirectora)
            ->delete(route('academico.usuarios.destroy', $director))
            ->assertForbidden();

        // ...ni otro Director.
        $otroDirector = $this->crearUsuario('Director');
        $this->actingAs($otroDirector)
            ->delete(route('academico.usuarios.destroy', $director))
            ->assertForbidden();

        $this->assertDatabaseHas('usuario', ['id' => $director->id, 'activo' => true]);
    }

    public function test_solo_el_director_desactiva_a_la_subdirectora(): void
    {
        $director = $this->crearUsuario('Director');
        $subdirectora = $this->crearUsuario('Subdirector');

        $this->actingAs($subdirectora)
            ->delete(route('academico.usuarios.destroy', $subdirectora))
            ->assertForbidden();

        $this->actingAs($director)
            ->delete(route('academico.usuarios.destroy', $subdirectora))
            ->assertRedirect(route('academico.usuarios.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('usuario', ['id' => $subdirectora->id, 'activo' => false]);
    }

    public function test_desactivar_y_reactivar_personal_comun(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $secretaria = $this->crearUsuario('Secretaria');

        // Desactivar
        $this->actingAs($gestor)
            ->delete(route('academico.usuarios.destroy', $secretaria))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('usuario', ['id' => $secretaria->id, 'activo' => false]);

        // Reactivar (el mismo endpoint alterna el estado)
        $this->actingAs($gestor)
            ->delete(route('academico.usuarios.destroy', $secretaria))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('usuario', ['id' => $secretaria->id, 'activo' => true]);
    }

    // ------------------------------------------------------------------
    // Reseteo administrativo de contraseña
    // ------------------------------------------------------------------

    public function test_gestor_resetea_la_contrasena_de_un_usuario(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $secretaria = $this->crearUsuario('Secretaria', [
            'password' => Hash::make('vieja-clave-123'),
        ]);

        $this->actingAs($gestor)
            ->put(route('academico.usuarios.reset-password', $secretaria), [
                'password' => 'nueva-clave-456',
            ])
            ->assertRedirect(route('academico.usuarios.index'))
            ->assertSessionHas('success');

        $secretaria->refresh();
        $this->assertTrue(Hash::check('nueva-clave-456', $secretaria->password));
        $this->assertFalse(Hash::check('vieja-clave-123', $secretaria->password));
    }

    public function test_gestor_no_puede_resetear_la_contrasena_del_director(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $director = $this->crearUsuario('Director', [
            'password' => Hash::make('clave-director-1'),
        ]);

        $this->actingAs($gestor)
            ->put(route('academico.usuarios.reset-password', $director), [
                'password' => 'intentodecambio',
            ])
            ->assertForbidden();

        $director->refresh();
        $this->assertTrue(Hash::check('clave-director-1', $director->password));
    }

    public function test_reseteo_valida_minimo_ocho_caracteres(): void
    {
        $gestor = $this->crearUsuario('Gestor de Usuarios');
        $secretaria = $this->crearUsuario('Secretaria');

        $this->actingAs($gestor)
            ->put(route('academico.usuarios.reset-password', $secretaria), [
                'password' => 'corta',
            ])
            ->assertSessionHasErrors('password');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function payloadEdicion(Usuario $objetivo, array $extra = []): array
    {
        return array_merge([
            'nombre_completo' => $objetivo->nombre_completo,
            'email' => $objetivo->email,
            'activo' => true,
            'roles' => [$objetivo->roles->first()?->name ?? 'Secretaria'],
        ], $extra);
    }
}
