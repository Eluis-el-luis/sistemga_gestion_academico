<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;


class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Director'],
            ['nombre' => 'Subdirector'],
            ['nombre' => 'Coordinador'],
            ['nombre' => 'Docente Guía'],
            ['nombre' => 'Docente por Asignatura'],
            ['nombre' => 'Alumno'],
        ];

        foreach ($roles as $rol) { Rol::create($rol); }
    }
}
