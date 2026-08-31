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
        $idTemaMotivador = Asignatura::where('nombre', 'Tema motivador')->value('id');

        foreach ($gradosPreescolar as $grado) {
            MallaCurricular::firstOrCreate(
                ['grado_id' => $grado->id, 'asignatura_id' => $idTemaMotivador],
                ['horas_semanales_sugeridas' => 20]
            );
        }

        // --- 2. Primaria y Secundaria (materias oficiales por grado) ---
        $mallaPorGrado = [
            '1ro' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Conociendo mi Mundo',
                'Derechos de la Mujer', 'TIC', 'Biblia',
            ],
            '2do' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Conociendo mi Mundo',
                'Derechos de la Mujer', 'TIC', 'Biblia',
            ],
            '3ro' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Ciencias Naturales',
                'Ciencias Sociales', 'Derechos de la Mujer', 'TIC', 'Biblia',
            ],
            '4to' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Ciencias Naturales',
                'Ciencias Sociales', 'Derechos de la Mujer', 'TIC', 'Biblia',
            ],
            '5to' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Ciencias Naturales',
                'Ciencias Sociales', 'Conociendo mi Mundo', 'Derechos de la Mujer',
                'TIC', 'Orientación Vocacional', 'Biblia',
            ],
            '6to' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Ciencias Naturales',
                'Ciencias Sociales', 'Conociendo mi Mundo', 'Derechos de la Mujer',
                'TIC', 'Biblia',
            ],
            '7mo' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Ciencias Naturales',
                'Geografía', 'Derechos de la Mujer', 'Historia',
                'TIC', 'Orientación Vocacional', 'Biblia',
            ],
            '8vo' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Ciencias Naturales',
                'Geografía', 'Derechos de la Mujer', 'Sociología', 'Historia',
                'TIC', 'Orientación Vocacional', 'Biblia',
            ],
            '9no' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Ciencias Naturales',
                'Geografía', 'Derechos de la Mujer', 'Historia', 'Economía', 'Filosofía',
                'TIC', 'Orientación Vocacional', 'Biblia',
            ],
            '10mo' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP',
                'Química', 'Física', 'Geografía', 'Derechos de la Mujer',
                'TIC', 'Orientación Vocacional', 'Biblia',
            ],
            '11mo' => [
                'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
                'Creciendo en Valores', 'Educación Física', 'AEP', 'Ciencias Naturales',
                'Química', 'Física', 'Geografía', 'Derechos de la Mujer', 'Sociología',
                'Historia', 'Economía', 'Filosofía',
                'TIC', 'Orientación Vocacional', 'Biblia',
            ],
        ];

        foreach ($mallaPorGrado as $nombreGrado => $materias) {
            $grado = Grado::where('nombre', $nombreGrado)->first();
            if (!$grado) {
                continue;
            }

            foreach ($materias as $nombreMateria) {
                $asignatura = Asignatura::where('nombre', $nombreMateria)->first();
                if (!$asignatura) {
                    continue;
                }

                MallaCurricular::firstOrCreate(
                    ['grado_id' => $grado->id, 'asignatura_id' => $asignatura->id],
                    ['horas_semanales_sugeridas' => $asignatura->es_extracurricular ? 2 : 4]
                );
            }
        }
    }
}