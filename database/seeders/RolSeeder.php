<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        // Catálogo de roles jerárquicos del colegio[cite: 1]
        $roles = [
            ['nombre' => 'Director'],
            ['nombre' => 'Subdirector'],
            ['nombre' => 'Coordinador'],
            ['nombre' => 'Docente Guia'],
            ['nombre' => 'Docente por Asignatura'],
            ['nombre' => 'Secretaria'],
            ['nombre' => 'Alumno'],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre' => $rol['nombre']]);
        }
    }
}