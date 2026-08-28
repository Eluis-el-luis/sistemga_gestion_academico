<?php

namespace Database\Factories;

use App\Models\AulaAsignaturaDocente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AulaAsignaturaDocente>
 */
class AulaAsignaturaDocenteFactory extends Factory
{
    protected $model = AulaAsignaturaDocente::class;

    public function definition(): array
    {
        return [
            'aula_id' => \App\Models\Aula::factory(),
            'asignatura_id' => \App\Models\Asignatura::factory(),
            'docente_id' => \App\Models\Docente::factory(),
            'anio_escolar_id' => \App\Models\AnioEscolar::factory(),
            'horas_semanales' => 4,
        ];
    }
}