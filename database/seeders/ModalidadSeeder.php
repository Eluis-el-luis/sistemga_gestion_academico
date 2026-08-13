<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Modalidad;

class ModalidadSeeder extends Seeder
{
    public function run(): void
    {
        $modalidades = [
            ['nombre' => 'Preescolar Regular'],
            ['nombre' => 'Primaria Regular'],
            ['nombre' => 'Secundaria Regular'],
        ];

        foreach ($modalidades as $modalidad) {
            Modalidad::create($modalidad);
        }
    }
}
