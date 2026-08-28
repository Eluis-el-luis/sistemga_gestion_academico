<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PermisoMatrizTest extends TestCase
{
    use RefreshDatabase;

    public function test_todos_los_permisos_referenciados_existen_en_seeder(): void
    {
        // Ejecutar seeders para poblar permisos
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\PermisoSeeder']);

        $permisosEnBD = \Spatie\Permission\Models\Permission::pluck('name')->toArray();

        // Recopilar todos los strings de permiso usados en Policies y Controllers
        $permisosReferenciados = $this->extraerPermisosDelCodigo();

        $faltantes = array_diff($permisosReferenciados, $permisosEnBD);

        $this->assertEmpty($faltantes, 'Los siguientes permisos se usan en código pero no existen en PermisoSeeder: ' . implode(', ', $faltantes));
    }

    protected function extraerPermisosDelCodigo(): array
    {
        $archivos = array_merge(
            File::glob(app_path('Policies/*.php')),
            File::glob(app_path('Http/Controllers/**/*.php')),
            File::glob(app_path('Http/Requests/**/*.php'))
        );

        $patron = '/(?:hasPermissionTo|can|authorize)\([\'"]([^\'"\)]+)[\'"]/';
        $permisos = [];

        foreach ($archivos as $archivo) {
            $contenido = File::get($archivo);
            preg_match_all($patron, $contenido, $matches);
            if (!empty($matches[1])) {
                $permisos = array_merge($permisos, $matches[1]);
            }
        }

        // Filtrar solo permisos de formato modulo.accion (no roles ni habilidades como 'calificar')
        $permisos = array_filter($permisos, fn($p) => str_contains($p, '.'));

        return array_unique($permisos);
    }
}