<?php

namespace Database\Factories;

use App\Models\Asignatura;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asignatura>
 */
class AsignaturaFactory extends Factory
{
    protected $model = Asignatura::class;

    public function definition(): array
    {
        static $n = 0;
        $n++;
        return [
            'nombre' => "Asignatura $n",
            'es_extracurricular' => false,
        ];
    }
}