<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Aula;
use App\Models\Grado;
use App\Models\Modalidad;
use App\Models\Docente;
use App\Models\AnioEscolar;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        $grado5to = Grado::where('nombre', '5to')->first()->id;
        $primaria = Modalidad::where('nombre', 'Primaria Regular')->first()->id;
        $docenteJuan = Docente::where('codigo_unico_persona', 'DOC-001')->first()->id;
        $anioActual = AnioEscolar::where('activo', true)->first()->id;

        Aula::create([
            'nombre' => '5to A',
            'grado_id' => $grado5to,
            'modalidad_id' => $primaria,
            'turno' => 'Matutino',
            'docente_guia_id' => $docenteJuan,
            'anio_escolar_id' => $anioActual,
            'cupo' => 30
        ]);
    }
}
