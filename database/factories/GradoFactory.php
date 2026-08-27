<?php

namespace Database\Factories;

use App\Models\Grado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grado>
 */
class GradoFactory extends Factory
{
    protected $model = Grado::class;

    public function definition(): array
    {
        static $n = 0;
        $n++;
        return [
            'nombre' => "Grado $n",
            'modalidad_id' => \App\Models\Modalidad::factory(),
        ];
    }
}