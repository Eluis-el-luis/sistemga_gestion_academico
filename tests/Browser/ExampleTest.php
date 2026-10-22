<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

/**
 * Test de humo de la infraestructura E2E:
 * valida que el servidor dedicado, la base de datos dusk.sqlite,
 * el sembrado del escenario y el login funcionan de punta a punta.
 */
class ExampleTest extends E2eTestCase
{
    public function test_login_como_director_llega_al_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginComo($browser, self::EMAIL_DIRECTOR)
                ->assertPathIs('/dashboard')
                ->assertSee('Roberto');
        });
    }

    public function test_credenciales_invalidas_rechazan_login(): void
    {
        $this->browse(function (Browser $browser) {
            $this->comoInvitado($browser)
                ->type('email', self::EMAIL_DIRECTOR)
                ->type('password', 'incorrecta')
                ->click('button[type="submit"]')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }

    public function test_huesped_sin_sesion_es_redirigido_al_login(): void
    {
        $this->browse(function (Browser $browser) {
            $this->comoInvitado($browser)
                ->visit('/dashboard')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }

    public function test_logout_real_cierra_sesion(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginComo($browser, self::EMAIL_DIRECTOR)
                ->assertPathIs('/dashboard');

            $this->logoutDe($browser)
                ->assertPathIs('/login');

            // La sesión debe estar realmente cerrada.
            $browser->visit('/dashboard')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }
}
