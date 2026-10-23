<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\IndicadorLogro;
use App\Models\Modalidad;

class IndicadorLogroSeeder extends Seeder
{
    public function run(): void
    {
        $preescolar = Modalidad::where('nombre', 'Preescolar Regular')->first()->id;
        $primaria = Modalidad::where('nombre', 'Primaria Regular')->first()->id;
        $secundaria = Modalidad::where('nombre', 'Secundaria Regular')->first()->id;

        // Preescolar: Cualitativo puro (Sin rango numérico)
        IndicadorLogro::create(['codigo' => 'AA', 'nombre' => 'Aprendizaje Avanzado', 'nota_min' => null, 'nota_max' => null, 'modalidad_id' => $preescolar]);
        IndicadorLogro::create(['codigo' => 'AP', 'nombre' => 'En Proceso', 'nota_min' => null, 'nota_max' => null, 'modalidad_id' => $preescolar]);

        // Escala numérica oficial MINED 2026 (AA, AS, AE, AI) para Primaria y Secundaria
        $escalaNumerica = [
            ['codigo' => 'AA', 'nombre' => 'Aprendizaje Avanzado', 'nota_min' => 90, 'nota_max' => 100],
            ['codigo' => 'AS', 'nombre' => 'Aprendizaje Satisfactorio', 'nota_min' => 76, 'nota_max' => 89],
            ['codigo' => 'AE', 'nombre' => 'Aprendizaje Esperado', 'nota_min' => 60, 'nota_max' => 75],
            ['codigo' => 'AI', 'nombre' => 'Aprendizaje Inicial', 'nota_min' => 0, 'nota_max' => 59],
        ];

        foreach ([$primaria, $secundaria] as $modalidadId) {
            foreach ($escalaNumerica as $indicador) {
                IndicadorLogro::create($indicador + ['modalidad_id' => $modalidadId]);
            }
        }
    }
}