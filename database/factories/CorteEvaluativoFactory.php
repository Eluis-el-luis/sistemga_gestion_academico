<?php

namespace Database\Factories;

use App\Models\CorteEvaluativo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorteEvaluativo>
 */
class CorteEvaluativoFactory extends Factory
{
    protected $model = CorteEvaluativo::class;

    public function definition(): array
    {
        static $n = 0;
        $n++;
        return [
            'anio_escolar_id' => \App\Models\AnioEscolar::factory(),
            'numero' => $n,
            'semestre' => ($n <= 2) ? 1 : 2,
            'fecha_inicio' => now()->startOfYear(),
            'fecha_fin' => now()->endOfYear(),
        ];
    }
}