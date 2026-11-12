<?php

namespace Tests\Feature\Authorization;

use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Aula;
use App\Models\Grado;
use App\Models\Modalidad;
use App\Models\AnioEscolar;
use App\Models\Matricula;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuthorizationLoggingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authorization_exception_se_loggea_correctamente()
    {
        Log::spy();
        
        $user = Usuario::factory()->create();
        $user->assignRole('Docente por Asignatura');
        // Sin permisos de notas
        
        $this->actingAs($user);
        
        // Intentar acceder a ruta que requiere permiso notas.ver
        $response = $this->get('/academico/notas');
        
        // Debe ser 403
        $response->assertStatus(403);
        
        // Verificar que se loggeó la excepción
        Log::shouldHaveReceived('warning')
            ->with('Authorization denied', \Mockery::on(function ($context) {
                return isset($context['user_id']) 
                    && isset($context['user_roles']) 
                    && isset($context['ip']) 
                    && isset($context['url']) 
                    && isset($context['ability']) 
                    && isset($context['timestamp']) 
                    && isset($context['trace_id']);
            }));
    }

    /** @test */
    public function log_incluye_roles_del_usuario()
    {
        Log::spy();
        
        $user = Usuario::factory()->create();
        $user->assignRole(['Coordinador', 'Docente Guia']);
        
        $this->actingAs($user);
        
        $response = $this->get('/academico/reportes');
        
        $response->assertStatus(403);
        
        Log::shouldHaveReceived('warning')
            ->with('Authorization denied', \Mockery::on(function ($context) {
                return in_array('Coordinador', $context['user_roles']) 
                    && in_array('Docente Guia', $context['user_roles']);
            }));
    }

    /** @test */
    public function log_incluye_ip_y_user_agent()
    {
        Log::spy();
        
        $user = Usuario::factory()->create();
        $user->assignRole('Director');
        
        $this->actingAs($user);
        
        $response = $this->withHeaders([
            'User-Agent' => 'TestAgent/1.0',
            'X-Forwarded-For' => '203.0.113.195',
        ])->get('/academico/usuarios');
        
        // Director tiene acceso, no debe loggear
        // Probar con usuario sin permiso
        $user2 = Usuario::factory()->create();
        $user2->assignRole('Docente por Asignatura');
        
        $this->actingAs($user2);
        
        $response = $this->withHeaders([
            'User-Agent' => 'TestAgent/1.0',
            'X-Forwarded-For' => '203.0.113.195',
        ])->get('/academico/usuarios');
        
        $response->assertStatus(403);
        
        Log::shouldHaveReceived('warning')
            ->with('Authorization denied', \Mockery::on(function ($context) {
                return $context['ip'] === '203.0.113.195' 
                    && $context['user_agent'] === 'TestAgent/1.0';
            }));
    }
}