<?php

namespace Database\Factories;

use App\Models\IndicadorLogro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndicadorLogro>
 */
class IndicadorLogroFactory extends Factory
{
    protected $model = IndicadorLogro::class;

    public function definition(): array
    {
        $codigos = ['AA', 'AS', 'AF', 'AI'];
        $nombres = ['Aprendizaje Avanzado', 'Aprendizaje Satisfactorio', 'Aprendizaje Fundamental', 'Aprendizaje Inicial'];
        $i = array_rand($codigos);
        
        return [
            'codigo' => $codigos[$i],
            'nombre' => $nombres[$i],
            'nota_min' => 0,
            'nota_max' => 100,
            'modalidad_id' => \App\Models\Modalidad::factory(),
            'grado_min' => 1,
            'grado_max' => 12,
        ];
    }
}