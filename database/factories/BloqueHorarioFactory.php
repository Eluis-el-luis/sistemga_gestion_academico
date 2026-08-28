<?php

namespace Database\Factories;

use App\Models\BloqueHorario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BloqueHorario>
 */
class BloqueHorarioFactory extends Factory
{
    protected $model = BloqueHorario::class;

    public function definition(): array
    {
        static $n = 0;
        $n++;
        return [
            'modalidad_id' => \App\Models\Modalidad::factory(),
            'turno' => 'Matutino',
            'nombre' => "Bloque $n",
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'es_recreo' => false,
        ];
    }
}