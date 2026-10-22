<?php

// Diagnóstico: POST duplicado de alumno y contenido de la respuesta.

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\Seeders\PermisoSeeder', '--force' => true]);

use App\Models\Usuario;

$director = Usuario::factory()->create();
$director->assignRole('Director');

$this_ = $this ?? null;

// Simular el HTTP request vía el kernel
$primerPayload = [
    'codigo_unico_persona' => 'DBG-000001',
    'nombre_completo' => 'Alumno Uno',
    'sexo' => 'M',
    'fecha_nacimiento' => '2019-04-12',
    'direccion_domiciliar' => 'Barrio Prueba',
];

$response = Illuminate\Support\Facades\Route::has('academico.alumnos.store') ? 'ruta ok' : 'sin ruta';
echo "Ruta: $response\n";

// Usar el testing HTTP del framework
$test = new class extends Illuminate\Foundation\Testing\TestCase {
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function ejecutar()
    {
        $director = \App\Models\Usuario::factory()->create();
        $director->assignRole('Director');

        $payload = [
            'codigo_unico_persona' => 'DBG-000001',
            'nombre_completo' => 'Alumno Uno',
            'sexo' => 'M',
            'fecha_nacimiento' => '2019-04-12',
            'direccion_domiciliar' => 'Barrio Prueba',
        ];

        $r1 = $this->actingAs($director)->post(route('academico.alumnos.store'), $payload);
        echo "POST 1 status: " . $r1->status() . "\n";

        $r2 = $this->actingAs($director)->post(route('academico.alumnos.store'), array_merge($payload, [
            'nombre_completo' => 'Alumno Dos',
        ]));
        echo "POST 2 (duplicado) status: " . $r2->status() . "\n";
        echo "POST 2 errores: " . json_encode($r2->getSession()->get('errors')?->getBag('default')->all()) . "\n";
        echo "POST 2 flash error: " . ($r2->getSession()->get('error') ?? '(ninguno)') . "\n";
        echo "POST 2 contenido (primeros 200): " . substr(strip_tags($r2->getContent()), 0, 200) . "\n";
    }
};

$test->setUp();
$test->ejecutar();
