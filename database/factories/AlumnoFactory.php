<?php

namespace Database\Factories;

use App\Models\Alumno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumno>
 */
class AlumnoFactory extends Factory
{
    protected $model = Alumno::class;

    public function definition(): array
    {
        return [
            'usuario_id' => null,
            'codigo_unico_persona' => fake()->unique()->numerify('##########'),
            'nombre_completo' => fake()->name(),
            'sexo' => fake()->randomElement(['M', 'F']),
            'fecha_nacimiento' => fake()->dateTimeBetween('-18 years', '-5 years'),
            'direccion_domiciliar' => fake()->address(),
            'enfermedades_cronicas' => null,
            'hermanos_en_colegio' => false,
            'madre_nombre_completo' => fake()->name('female'),
            'madre_cedula' => fake()->unique()->numerify('##########'),
            'madre_telefono' => fake()->phoneNumber(),
            'madre_ocupacion' => fake()->jobTitle(),
            'madre_asiste_iglesia' => fake()->boolean(),
            'madre_nombre_iglesia' => fake()->company(),
            'padre_nombre_completo' => fake()->name('male'),
            'padre_cedula' => fake()->unique()->numerify('##########'),
            'padre_telefono' => fake()->phoneNumber(),
            'padre_ocupacion' => fake()->jobTitle(),
            'padre_asiste_iglesia' => fake()->boolean(),
            'padre_nombre_iglesia' => fake()->company(),
            'tutor_nombre_completo' => fake()->name(),
            'tutor_cedula' => fake()->unique()->numerify('##########'),
            'tutor_telefono' => fake()->phoneNumber(),
            'tutor_ocupacion' => fake()->jobTitle(),
            'autorizado_retirar_nombre' => fake()->name(),
            'autorizado_retirar_cedula' => fake()->unique()->numerify('##########'),
            'autorizado_retirar_telefono' => fake()->phoneNumber(),
            'acepta_compromiso_cristiano' => true,
        ];
    }
}