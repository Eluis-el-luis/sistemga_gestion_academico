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
            ['codigo_unico_persona' => 'ALU-003', 'nombre_completo' => 'Eloisa Lanzas', 'sexo' => 'F', 'fecha_nacimiento' => '2015-09-28'],
            ['codigo_unico_persona' => 'ALU-004', 'nombre_completo' => 'Luis Meneses', 'sexo' => 'M', 'fecha_nacimiento' => '2015-10-15'],
            ['codigo_unico_persona' => 'ALU-005', 'nombre_completo' => 'Alejandro Picon', 'sexo' => 'M', 'fecha_nacimiento' => '2015-04-07'],
            ['codigo_unico_persona' => 'ALU-006', 'nombre_completo' => 'Yosser Mercado', 'sexo' => 'M', 'fecha_nacimiento' => '2015-11-30'],
            ['codigo_unico_persona' => 'ALU-007', 'nombre_completo' => 'Diana Ocampos', 'sexo' => 'F', 'fecha_nacimiento' => '2015-11-01'],
        ];

        foreach ($alumnos as $alumno) { Alumno::create($alumno); }
    }
}
