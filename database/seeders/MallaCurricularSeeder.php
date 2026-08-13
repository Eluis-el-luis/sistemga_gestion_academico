<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\MallaCurricular;
use App\Models\Grado;
use App\Models\Asignatura;

class MallaCurricularSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Preescolar ---
        $gradosPreescolar = Grado::whereIn('nombre', ['I Nivel', 'II Nivel', 'III Nivel'])->get();
        $idTemaMotivador = Asignatura::where('nombre', 'Tema motivador')->first()->id;

        foreach ($gradosPreescolar as $grado) {
            MallaCurricular::create([
                'grado_id' => $grado->id,
                'asignatura_id' => $idTemaMotivador,
                'horas_semanales_sugeridas' => 20
            ]);
        }

        // --- 2. Ejemplo: 1ro de Primaria ---
        // (Deberás buscar los IDs de las materias y grados correspondientes a la tabla)
        $grado1ro = Grado::where('nombre', '1ro')->first()->id;
        
        // Materias de 1ro según el Anexo
        $materias1ro = [
            'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC', 
            'Creciendo en Valores', 'Educación Física', 'AEP', 'Conociendo mi Mundo',
            'Derechos de la Mujer', 'TIC', 'Biblia'
        ];
        
        foreach ($materias1ro as $nombreMateria) {
            $asignatura = Asignatura::where('nombre', $nombreMateria)->first();
            if ($asignatura) {
                MallaCurricular::create([
                    'grado_id' => $grado1ro,
                    'asignatura_id' => $asignatura->id,
                    'horas_semanales_sugeridas' => 4 
                ]);
            }
        }
    }
}
