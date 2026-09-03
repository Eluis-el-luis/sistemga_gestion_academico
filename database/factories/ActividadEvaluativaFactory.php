<?php

namespace Database\Factories;

use App\Models\ActividadEvaluativa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActividadEvaluativa>
 */
class ActividadEvaluativaFactory extends Factory
{
    protected $model = ActividadEvaluativa::class;

    public function definition(): array
    {
        return [
            'aula_asignatura_docente_id' => \App\Models\AulaAsignaturaDocente::factory(),
            'corte_evaluativo_id' => \App\Models\CorteEvaluativo::factory(),
            'nombre' => fake()->words(2, true),
            'descripcion' => null,
            'puntaje_maximo' => 50,
            'fecha' => now()->toDateString(),
        ];
    }
}