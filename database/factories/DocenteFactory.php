<?php

namespace Database\Factories;

use App\Models\Docente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Docente>
 */
class DocenteFactory extends Factory
{
    protected $model = Docente::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(),
            'codigo_unico_persona' => fake()->unique()->numerify('##########'),
            'sexo' => fake()->randomElement(['M', 'F']),
            'es_coordinador' => false,
            'modalidad_coordina_id' => null,
        ];
    }
}