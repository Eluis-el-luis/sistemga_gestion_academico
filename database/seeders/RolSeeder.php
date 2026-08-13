<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Director'],
            ['nombre' => 'Subdirector'],
            ['nombre' => 'Docente'],
            ['nombre' => 'Alumno'],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}