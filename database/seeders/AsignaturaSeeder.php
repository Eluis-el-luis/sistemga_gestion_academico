<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Asignatura;

class AsignaturaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Materia exclusiva de Preescolar
        Asignatura::firstOrCreate(
            ['nombre' => 'Tema motivador'],
            ['area' => 'Preescolar', 'es_extracurricular' => false]
        );

        // 2. Catálogo oficial con áreas (Primaria y Secundaria)
        $materias = [
            // Matemática
            ['nombre' => 'Matemática', 'area' => 'Matemática', 'es_extracurricular' => false],
            // Comunicación
            ['nombre' => 'Lengua y Literatura', 'area' => 'Comunicación', 'es_extracurricular' => false],
            ['nombre' => 'Lengua Extranjera', 'area' => 'Comunicación', 'es_extracurricular' => false],
            // Ciencias Naturales
            ['nombre' => 'Conociendo mi Mundo', 'area' => 'Ciencias Naturales', 'es_extracurricular' => false],
            ['nombre' => 'Ciencias Naturales', 'area' => 'Ciencias Naturales', 'es_extracurricular' => false],
            ['nombre' => 'Química', 'area' => 'Ciencias Naturales', 'es_extracurricular' => false],
            ['nombre' => 'Física', 'area' => 'Ciencias Naturales', 'es_extracurricular' => false],
            // Ciencias Sociales
            ['nombre' => 'Ciencias Sociales', 'area' => 'Ciencias Sociales', 'es_extracurricular' => false],
            ['nombre' => 'Geografía', 'area' => 'Ciencias Sociales', 'es_extracurricular' => false],
            ['nombre' => 'Historia', 'area' => 'Ciencias Sociales', 'es_extracurricular' => false],
            ['nombre' => 'Sociología', 'area' => 'Ciencias Sociales', 'es_extracurricular' => false],
            ['nombre' => 'Economía', 'area' => 'Ciencias Sociales', 'es_extracurricular' => false],
            ['nombre' => 'Filosofía', 'area' => 'Ciencias Sociales', 'es_extracurricular' => false],
            // Tecnología Educativa
            ['nombre' => 'TAC', 'area' => 'Tecnología Educativa', 'es_extracurricular' => false],
            ['nombre' => 'AEP', 'area' => 'Tecnología Educativa', 'es_extracurricular' => false],
            ['nombre' => 'TIC', 'area' => 'Tecnología Educativa', 'es_extracurricular' => true],
            // Formación Personal, Social y Espiritual
            ['nombre' => 'Creciendo en Valores', 'area' => 'Formación Personal, Social y Espiritual', 'es_extracurricular' => false],
            ['nombre' => 'Educación Física', 'area' => 'Formación Personal, Social y Espiritual', 'es_extracurricular' => false],
            ['nombre' => 'Derechos de la Mujer', 'area' => 'Formación Personal, Social y Espiritual', 'es_extracurricular' => false],
            ['nombre' => 'Orientación Vocacional', 'area' => 'Formación Personal, Social y Espiritual', 'es_extracurricular' => true],
            ['nombre' => 'Biblia', 'area' => 'Formación Personal, Social y Espiritual', 'es_extracurricular' => true],
        ];

        foreach ($materias as $materia) {
            Asignatura::firstOrCreate(
                ['nombre' => $materia['nombre']],
                ['area' => $materia['area'], 'es_extracurricular' => $materia['es_extracurricular']]
            );
        }
    }
}