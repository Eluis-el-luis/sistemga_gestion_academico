<?php

namespace Database\Factories;

use App\Models\AnioEscolar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnioEscolar>
 */
class AnioEscolarFactory extends Factory
{
    protected $model = AnioEscolar::class;

    public function definition(): array
    {
        static $n = 2020;
        $n++;
        return [
            'nombre' => (string)$n,
            'fecha_inicio' => now()->startOfYear(),
            'fecha_fin' => now()->endOfYear(),
            'activo' => false,
        ];
    }
}