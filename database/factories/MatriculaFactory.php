<?php

namespace Database\Factories;

use App\Models\Matricula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matricula>
 */
class MatriculaFactory extends Factory
{
    protected $model = Matricula::class;

    public function definition(): array
    {
        return [
            'alumno_id' => \App\Models\Alumno::factory(),
            'aula_id' => \App\Models\Aula::factory(),
            'anio_escolar_id' => \App\Models\AnioEscolar::factory(),
            'estado' => 'activo',
            'fecha_matricula' => now(),
        ];
    }
}