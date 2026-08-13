<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Alumno;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        // Alumnos sin usuario de sistema asignado todavía
        $alumnos = [
            ['codigo_unico_persona' => 'ALU-001', 'nombre_completo' => 'Carlos López', 'sexo' => 'M', 'fecha_nacimiento' => '2015-05-10'],
            ['codigo_unico_persona' => 'ALU-002', 'nombre_completo' => 'María García', 'sexo' => 'F', 'fecha_nacimiento' => '2015-08-22'],
        ];

        foreach ($alumnos as $alumno) { Alumno::create($alumno); }
    }
}
