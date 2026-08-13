<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Asignatura;

class AsignaturaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Materia exclusiva de Preescolar
        Asignatura::create(['nombre' => 'Tema motivador', 'es_extracurricular' => false]);

        // 2. Catálogo Regular (Primaria/Secundaria)
        $materias = [
            'Matemática', 'Lengua y Literatura', 'Lengua Extranjera', 'TAC', 
            'Creciendo en Valores', 'Educación Física', 'AEP', 'Conociendo mi Mundo', 
            'Ciencias Naturales', 'Ciencias Sociales', 'Geografía', 'Historia', 
            'Sociología', 'Economía', 'Filosofía', 'Química', 'Física', 'Derechos de la Mujer'
        ];

        foreach ($materias as $materia) {
            Asignatura::create(['nombre' => $materia, 'es_extracurricular' => false]);
        }

        // 3. Extracurriculares
        $extracurriculares = ['TIC', 'Orientación Vocacional', 'Biblia'];
        foreach ($extracurriculares as $extra) {
            Asignatura::create(['nombre' => $extra, 'es_extracurricular' => true]);
        }
    }
}