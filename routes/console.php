<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Respaldos automáticos (módulo creado tras el incidente de Oct 2026)
|--------------------------------------------------------------------------
| Requiere que "php artisan schedule:work" esté corriendo (incluido en
| composer dev). Genera un respaldo diario a la hora configurada en
| BACKUP_HORA (config/respaldos.php) y aplica la retención automáticamente.
*/
Schedule::command('respaldos:crear --motivo=automatico-diario')
    ->dailyAt(config('respaldos.hora_automatica', '02:00'))
    ->withoutOverlapping()
    ->onFailure(function () {
        logger()->error('El respaldo automático diario falló.');
    });
