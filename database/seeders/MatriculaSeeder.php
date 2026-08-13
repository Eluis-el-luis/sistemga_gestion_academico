<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\AnioEscolar;

class MatriculaSeeder extends Seeder
{
    public function run(): void
    {
        $aula5toA = Aula::where('nombre', '5to A')->first()->id;
        $anioActual = AnioEscolar::where('activo', true)->first()->id;
        $alumnos = Alumno::all();

        foreach ($alumnos as $alumno) {
            Matricula::create([
                'alumno_id' => $alumno->id,
                'aula_id' => $aula5toA,
                'anio_escolar_id' => $anioActual,
                'estado' => 'activo',
                'fecha_matricula' => '2026-02-01'
            ]);
        }
    }
}
