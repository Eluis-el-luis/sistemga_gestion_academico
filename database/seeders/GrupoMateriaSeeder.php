<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GrupoMateria;
use App\Models\Asignatura;

class GrupoMateriaSeeder extends Seeder
{
    public function run(): void
    {
        $grupos = [
            ['nombre' => 'Desarrollo Personal, Social y Emocional', 'orden' => 1],
            ['nombre' => 'Desarrollo de las Habilidades de la Comunicación y el Talento Artístico y Cultural', 'orden' => 2],
            ['nombre' => 'Desarrollo del Pensamiento Lógico y Científico', 'orden' => 3],
            ['nombre' => 'Otros', 'orden' => 4],
        ];

        $ids = [];
        foreach ($grupos as $grupo) {
            $g = GrupoMateria::firstOrCreate(['nombre' => $grupo['nombre']], ['orden' => $grupo['orden']]);
            $ids[$grupo['nombre']] = $g->id;
        }

        // Mapeo de asignaturas a grupo (según formato oficial del colegio)
        $mapeo = [
            'Desarrollo Personal, Social y Emocional' => [
                'Creciendo en Valores', 'Derechos de la Mujer', 'Educación Física', 'AEP',
                'Ciencias Sociales', 'Orientación Vocacional',
            ],
            'Desarrollo de las Habilidades de la Comunicación y el Talento Artístico y Cultural' => [
                'Lengua y Literatura', 'Lengua Extranjera', 'TAC',
            ],
            'Desarrollo del Pensamiento Lógico y Científico' => [
                'Matemática', 'Ciencias Naturales', 'Conociendo mi Mundo', 'Química', 'Física',
                'Biblia', 'TIC',
            ],
        ];

        foreach ($mapeo as $nombreGrupo => $materias) {
            $grupoId = $ids[$nombreGrupo] ?? null;
            if (!$grupoId) continue;

            foreach ($materias as $nombreMateria) {
                Asignatura::where('nombre', $nombreMateria)
                    ->update(['grupo_materia_id' => $grupoId]);
            }
        }

        // Las materias restantes sin grupo quedan en "Otros" (o null).
    }
}