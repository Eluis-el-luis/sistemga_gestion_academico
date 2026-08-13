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

        // Preescolar: Cualitativo puro (Sin rango numérico)
        IndicadorLogro::create(['codigo' => 'AA', 'nombre' => 'Aprendizaje Avanzado', 'nota_min' => null, 'nota_max' => null, 'modalidad_id' => $preescolar]);
        IndicadorLogro::create(['codigo' => 'AP', 'nombre' => 'En Proceso', 'nota_min' => null, 'nota_max' => null, 'modalidad_id' => $preescolar]);

        // Primaria (Y podrías repetir para secundaria)
        IndicadorLogro::create(['codigo' => 'AA', 'nombre' => 'Aprendizaje Avanzado', 'nota_min' => 90, 'nota_max' => 100, 'modalidad_id' => $primaria]);
        IndicadorLogro::create(['codigo' => 'AS', 'nombre' => 'Satisfactorio', 'nota_min' => 76, 'nota_max' => 89, 'modalidad_id' => $primaria]);
    }
}