<?php

namespace Tests\Browser;

use Database\Seeders\E2eScenarioSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Laravel\Dusk\Browser;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tests\DuskTestCase;

/**
 * Base para todas las pruebas E2E del sistema (Laravel Dusk sobre Chrome).
 *
 * Responsabilidades:
 *  1. Verificar que el servidor dedicado está arriba en 127.0.0.1:8299.
 *     Se inicia por separado (composer e2e) con:
 *       php artisan serve --env=dusk --host=127.0.0.1 --port=8299 --no-reload
 *  2. Gestionar la base de datos dusk.sqlite vía el trait DatabaseTruncation:
 *     - Primer test del proceso: migrate:fresh + E2eScenarioSeeder (que siembra
 *       catálogos + escenario).
 *     - Tests siguientes: trunca SOLO las tablas de negocio (los catálogos y
 *       roles/permisos persisten) y vuelve a sembrar el escenario. Rápido.
 *  3. Helpers de login/logout reales por navegador.
 *
 * Credenciales: ver Database\Seeders\E2eScenarioSeeder (password: 'password').
 */
abstract class E2eTestCase extends DuskTestCase
{
    use DatabaseTruncation;

    public const PASSWORD = 'password';

    public const EMAIL_DIRECTOR = 'director.e2e@ccn.test';
    public const EMAIL_SUBDIRECTOR = 'subdirector.e2e@ccn.test';
    public const EMAIL_GESTOR = 'gestor.e2e@ccn.test';
    public const EMAIL_COORDINADOR = 'coordinador.e2e@ccn.test';
    public const EMAIL_SECRETARIA = 'secretaria.e2e@ccn.test';
    public const EMAIL_DOCENTE_GUIA = 'guia.e2e@ccn.test';
    public const EMAIL_DOCENTE_MATERIA = 'materia.e2e@ccn.test';
    public const EMAIL_ALUMNO = 'alumno.e2e@ccn.test';

    protected const HOST = '127.0.0.1';
    protected const PORT = 8299;

    /**
     * Tablas que SOBREVIVEN al truncate entre tests: catálogos del sistema
     * y roles/permisos de Spatie. El resto (negocio) se limpia en cada test.
     */
    protected array $exceptTables = [
        // Catálogos académicos
        'modalidad',
        'anio_escolar',
        'grado',
        'asignatura',
        'grupo_materia',
        'malla_curricular',
        'indicador_logro',
        'corte_evaluativo',
        'bloque_horario',
        // Spatie Permission
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
    ];

    /** Seeder que DatabaseTruncation ejecuta tras cada truncate. */
    protected function seeder(): string
    {
        return E2eScenarioSeeder::class;
    }

    /**
     * SALVAGUARDA DE SEGURIDAD: nunca operar contra una conexión que no sea
     * el archivo dusk.sqlite. Defensa en profundidad ante variables DB_* del
     * shell del desarrollador (phpunit.dusk.xml ya usa force="true").
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'sqlite'
            || ! str_contains((string) config('database.connections.sqlite.database'), 'dusk.sqlite')) {
            $this->fail(
                'SEGURIDAD: las pruebas E2E solo pueden correr sobre database/dusk.sqlite. '
                . 'Conexión activa: "' . config('database.default') . '" => "'
                . config('database.connections.sqlite.database') . '". '
                . 'Revisa phpunit.dusk.xml y las variables DB_* de tu terminal.'
            );
        }

        if (! static::servidorResponde()) {
            static::fail(
                'No hay servidor E2E en ' . static::urlApp() . ".\n" .
                'Levántalo antes de correr los tests (composer e2e o):' . "\n" .
                '  php artisan serve --env=dusk --host=127.0.0.1 --port=8299 --no-reload'
            );
        }
    }

    /**
     * SALVAGUARDA EN TIEMPO DE EJECUCIÓN: comprueba que el servidor dedicado
     * está leyendo la MISMA base de datos que los tests (dusk.sqlite). Si el
     * servidor hubiera arrancado con otra conexión (p. ej. DB_* del shell
     * apuntando a la BD real), el login del centinela fallaría y se aborta
     * ANTES de que ningún test escriba nada por la UI.
     */
    protected static function verificarServidorUsaDusk(): void
    {
        $jar = tempnam(sys_get_temp_dir(), 'duskjar');

        $ch = curl_init(static::urlApp() . '/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_COOKIEJAR => $jar,
            CURLOPT_COOKIEFILE => $jar,
            CURLOPT_TIMEOUT => 10,
        ]);
        $html = (string) curl_exec($ch);
        curl_close($ch);

        if (! preg_match('/name="csrf-token" content="([^"]+)"/', $html, $m)) {
            @unlink($jar);
            static::fail('El servidor E2E no devolvió una página de login válida en ' . static::urlApp() . '.');
        }

        $ch = curl_init(static::urlApp() . '/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $jar,
            CURLOPT_COOKIEFILE => $jar,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                '_token' => $m[1],
                'email' => self::EMAIL_DIRECTOR,
                'password' => self::PASSWORD,
            ]),
        ]);
        curl_exec($ch);
        $urlFinal = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        $codigo = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        @unlink($jar);

        // Login correcto => redirección al dashboard (el usuario solo existe en dusk.sqlite).
        if ($codigo === 419 || ($codigo >= 300 && $codigo < 400 && ! str_contains($urlFinal, '/dashboard'))) {
            static::fail(
                'SEGURIDAD: el servidor E2E de ' . static::urlApp() . ' NO está leyendo database/dusk.sqlite '
                . "(el login del centinela falló, código $codigo).\n"
                . 'Cárralo con el entorno forzado:  scripts\\servidor-e2e.cmd'
            );
        }
    }

    /** La preparación de dusk.sqlite corre una sola vez por proceso de tests. */
    protected static bool $bdPreparada = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // ChromeDriver :9515

        if (! static::$bdPreparada) {
            static::asegurarServidorApp();
            static::prepararBaseDatos();
            static::verificarServidorUsaDusk();
            static::$bdPreparada = true;
        }
    }

    /**
     * Prepara dusk.sqlite una vez por proceso: migrate:fresh + catálogos +
     * escenario E2E. Corre en un subproceso (termina solo; no cuelga la
     * ejecución) heredando las variables forzadas de phpunit.dusk.xml.
     */
    protected static function prepararBaseDatos(): void
    {
        $raiz = dirname(__DIR__, 2);

        if (! file_exists($raiz . '/database/dusk.sqlite')) {
            touch($raiz . '/database/dusk.sqlite');
        }

        $proceso = new Process(
            [PHP_BINARY, 'artisan', 'migrate:fresh', '--seed', '--force'],
            $raiz,
        );
        $proceso->setTimeout(600);

        try {
            $proceso->mustRun();
        } catch (ProcessFailedException $e) {
            static::fail(
                "No se pudo preparar database/dusk.sqlite:\n" . $proceso->getOutput() . $proceso->getErrorOutput()
            );
        }

        // El escenario E2E (cuentas *.e2e@ccn.test, aula, matrículas) debe
        // existir ANTES del centinela de login de setUpBeforeClass.
        $proceso = new Process(
            [PHP_BINARY, 'artisan', 'db:seed', '--class=' . E2eScenarioSeeder::class, '--force'],
            $raiz,
        );
        $proceso->setTimeout(600);

        try {
            $proceso->mustRun();
        } catch (ProcessFailedException $e) {
            static::fail(
                "No se pudo sembrar el escenario E2E:\n" . $proceso->getOutput() . $proceso->getErrorOutput()
            );
        }

        // La BD ya está migrada+sembrada: el trait DatabaseTruncation saltará
        // su propio migrate:fresh y solo truncará + re-sembrará el escenario.
        RefreshDatabaseState::$migrated = true;
    }

    // ------------------------------------------------------------------
    // Servidor de la aplicación
    // ------------------------------------------------------------------

    protected static function urlApp(): string
    {
        return 'http://' . static::HOST . ':' . static::PORT;
    }

    protected static function servidorResponde(): bool
    {
        $contexto = stream_context_create(['http' => ['timeout' => 8]]);

        // El php -S es mono-hilo: una conexión keep-alive del navegador puede
        // retrasar la respuesta de /up varios segundos. Reintentamos antes de
        // declarar al servidor caído (evita falsos "no hay servidor").
        for ($intento = 0; $intento < 3; $intento++) {
            if (@file_get_contents(static::urlApp() . '/up', false, $contexto) !== false) {
                return true;
            }
            sleep(1);
        }

        return false;
    }

    /**
     * Verifica que el servidor dedicado esté arriba. NO lo levanta: en Windows
     * un proceso hijo de PHPUnit colgaría la ejecución de los tests.
     */
    protected static function asegurarServidorApp(): void
    {
        if (static::servidorResponde()) {
            return;
        }

        static::fail(
            'No hay servidor E2E en ' . static::urlApp() . ".\n" .
            'Levántalo antes de correr los tests (composer e2e o):' . "\n" .
            '  php artisan serve --env=dusk --host=127.0.0.1 --port=8299 --no-reload'
        );
    }

    // ------------------------------------------------------------------
    // Helpers de navegación
    // ------------------------------------------------------------------

    /**
     * Login real por navegador y espera del dashboard.
     * Siempre parte de estado invitado (el navegador se reutiliza entre tests
     * y puede arrastrar una sesión previa que redirigiría /login → /dashboard).
     */
    protected function loginComo(Browser $browser, string $email): Browser
    {
        $browser->visit('/login')
            ->deleteCookie(config('session.cookie'))
            ->visit('/login')
            ->type('email', $email)
            ->type('password', self::PASSWORD)
            ->click('button[type="submit"]');

        return $browser->waitForLocation('/dashboard', 15);
    }

    /**
     * Garantiza estado de invitado eliminando la cookie de sesión.
     * Necesario porque Dusk reutiliza el navegador entre tests de la clase.
     */
    protected function comoInvitado(Browser $browser): Browser
    {
        return $browser->visit('/login') // con sesión activa redirige a /dashboard (mismo dominio)
            ->deleteCookie(config('session.cookie'))
            ->visit('/login');
    }

    /**
     * Marca un input radio oculto con Tailwind "sr-only" mediante JS.
     * WebDriver no puede hacer click físico sobre radios con clip de 1px.
     */
    protected function marcarRadio(Browser $browser, string $name, string $valor): void
    {
        $selector = 'input[name="' . $name . '"][value="' . $valor . '"]';

        $browser->script("document.querySelector('" . $selector . "')?.click();");
    }

    /**
     * Abre un modal de Alpine despachando el evento de ventana que usa la UI
     * (los botones reales hacen $dispatch('<evento>') sobre window).
     */
    protected function abrirModal(Browser $browser, string $evento): void
    {
        $browser->script("window.dispatchEvent(new CustomEvent('" . $evento . "'))");
    }

    /**
     * Cierra la sesión enviando el POST /logout real con el token CSRF del layout.
     */
    protected function logoutDe(Browser $browser): Browser
    {
        $browser->script(
            "const t = document.querySelector('meta[name=\"csrf-token\"]')?.content;
             const f = document.createElement('form');
             f.method = 'POST'; f.action = '/logout';
             const i = document.createElement('input');
             i.name = '_token'; i.value = t; f.appendChild(i);
             document.body.appendChild(f); f.submit();"
        );

        return $browser->waitForLocation('/login', 10);
    }
}
