<?php

namespace Database\Factories;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aula>
 */
class AulaFactory extends Factory
{
    protected $model = Aula::class;

    public function definition(): array
    {
        static $n = 0;
        $n++;
        return [
            'nombre' => "Aula $n",
            'grado_id' => \App\Models\Grado::factory(),
            'modalidad_id' => \App\Models\Modalidad::factory(),
            'turno' => 'Matutino',
            'docente_guia_id' => \App\Models\Docente::factory(),
            'anio_escolar_id' => \App\Models\AnioEscolar::factory(),
            'cupo' => 30,
        ];
    }
}