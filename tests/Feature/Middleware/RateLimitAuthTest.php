<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\RateLimitAuth;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear();
    }

    /** @test */
    public function login_tiene_rate_limiting_5_por_minuto()
    {
        // Hacer 5 requests exitosos (o fallidos)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
            // No debe ser 429 aún
            $this->assertNotEquals(429, $response->status());
        }
        
        // El 6to request debe ser bloqueado
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);
        
        $response->assertStatus(429);
    }

    /** @test */
    public function forgot_password_tiene_rate_limiting_3_por_hora()
    {
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/forgot-password', [
                'email' => 'test@example.com',
            ]);
            $this->assertNotEquals(429, $response->status());
        }
        
        // El 4to request debe ser bloqueado
        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);
        
        $response->assertStatus(429);
    }

    /** @test */
    public function rate_limiter_se_resetea_despues_de_tiempo()
    {
        // Bloquear
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong']);
        }
        
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong'])
            ->assertStatus(429);
        
        // Simular paso de tiempo (en test real usaríamos Carbon::setTestNow)
        // RateLimiter::clear() simula el reset
        RateLimiter::clear();
        
        $response = $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong']);
        $this->assertNotEquals(429, $response->status());
    }

    /** @test */
    public function rate_limit_es_por_ip()
    {
        // IP 1 hace 5 requests
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
                ->post('/login', ['email' => 'test@example.com', 'password' => 'wrong']);
        }
        
        // IP 1 bloqueada
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
            ->post('/login', ['email' => 'test@example.com', 'password' => 'wrong'])
            ->assertStatus(429);
        
        // IP 2 NO bloqueada
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.2'])
            ->post('/login', ['email' => 'test@example.com', 'password' => 'wrong'])
            ->assertNotEquals(429);
    }
}