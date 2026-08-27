<?php

namespace Database\Factories;

use App\Models\Modalidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Modalidad>
 */
class ModalidadFactory extends Factory
{
    protected $model = Modalidad::class;

    public function definition(): array
    {
        static $n = 0;
        $n++;
        return [
            'nombre' => "Modalidad $n",
        ];
    }
}