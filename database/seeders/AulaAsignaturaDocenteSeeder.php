<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\AulaAsignaturaDocente;
use App\Models\Aula;
use App\Models\Asignatura;
use App\Models\Docente;
use App\Models\AnioEscolar;

class AulaAsignaturaDocenteSeeder extends Seeder
{
    public function run(): void
    {
        $aula5toA = Aula::where('nombre', '5to A')->first()->id;
        $asignaturaMate = Asignatura::where('nombre', 'Matemática')->first()->id;
        $docenteJuan = Docente::where('codigo_unico_persona', 'DOC-001')->first()->id;
        $anioActual = AnioEscolar::where('activo', true)->first()->id;

        AulaAsignaturaDocente::create([
            'aula_id' => $aula5toA,
            'asignatura_id' => $asignaturaMate,
            'docente_id' => $docenteJuan,
            'anio_escolar_id' => $anioActual,
            'horas_semanales' => 5
        ]);
    }
}
