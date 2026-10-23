<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (static::runningInSail()) {
            return;
        }

        // ChromeDriver puede morir entre clases de tests (crash del binario).
        // Reusarlo si responde; si no, arrancarlo de nuevo y esperar a que
        // enlace el puerto (startChromeDriver no espera, ~2-3 s en Windows).
        if (static::chromeDriverResponde()) {
            return;
        }

        for ($intento = 1; $intento <= 2; $intento++) {
            static::startChromeDriver(['--port=9515']);

            $limite = microtime(true) + 20;
            while (microtime(true) < $limite) {
                if (static::chromeDriverResponde()) {
                    return;
                }
                usleep(300_000);
            }
        }

        fail('ChromeDriver no quedó disponible en el puerto 9515 tras 2 intentos.');
    }

    protected static function chromeDriverResponde(): bool
    {
        $conex = @fsockopen('127.0.0.1', 9515, $errno, $errstr, 1);
        if (is_resource($conex)) {
            fclose($conex);

            return true;
        }

        return false;
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
