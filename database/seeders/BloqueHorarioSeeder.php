<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BloqueHorario;
use App\Models\Modalidad;

class BloqueHorarioSeeder extends Seeder
{
    public function run(): void
    {
        // Buscamos la modalidad de Primaria (Ajusta el nombre si en tu BD es diferente)
        $primaria = Modalidad::where('nombre', 'like', '%Primaria%')->first();

        if ($primaria) {
            $bloques = [
                ['nombre' => '1ra Hora', 'hora_inicio' => '07:00', 'hora_fin' => '07:45', 'es_recreo' => false],
                ['nombre' => '2da Hora', 'hora_inicio' => '07:45', 'hora_fin' => '08:30', 'es_recreo' => false],
                ['nombre' => 'Receso', 'hora_inicio' => '08:30', 'hora_fin' => '09:00', 'es_recreo' => true],
                ['nombre' => '3ra Hora', 'hora_inicio' => '09:00', 'hora_fin' => '09:45', 'es_recreo' => false],
            ];

            foreach ($bloques as $bloque) {
                BloqueHorario::create(array_merge($bloque, [
                    'modalidad_id' => $primaria->id,
                    'turno' => 'Matutino'
                ]));
            }
        }
    }
}