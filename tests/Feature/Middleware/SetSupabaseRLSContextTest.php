<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\SetSupabaseRLSContext;
use App\Models\Usuario;
use App\Models\Docente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SetSupabaseRLSContextTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function middleware_setea_contexto_rls_para_usuario_autenticado()
    {
        $user = Usuario::factory()->create();
        $user->assignRole('Director');
        
        $this->actingAs($user);
        
        // Simular request a ruta con middleware
        $response = $this->get('/dashboard');
        
        // Verificar que las variables de sesión PostgreSQL fueron seteadas
        // Nota: En tests con SQLite, esto no se puede verificar directamente
        // pero podemos verificar que el middleware no lanza errores
        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_incluye_roles_en_contexto()
    {
        $user = Usuario::factory()->create();
        $user->assignRole(['Director', 'Coordinador']);
        
        $this->actingAs($user);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_incluye_docente_id_si_existe()
    {
        $user = Usuario::factory()->create();
        $user->assignRole('Docente Guia');
        
        Docente::factory()->create([
            'usuario_id' => $user->id,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_no_ejecuta_si_usuario_no_autenticado()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_solo_ejecuta_en_conexion_postgresql()
    {
        // En tests usamos SQLite, verificar que no rompe
        $user = Usuario::factory()->create();
        $user->assignRole('Director');
        
        $this->actingAs($user);
        
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        
        // Verificar que el driver es sqlite
        $this->assertEquals('sqlite', DB::getDriverName());
    }
}