<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Grado;
use App\Models\Modalidad;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        $preescolar = Modalidad::where('nombre', 'Preescolar Regular')->first()->id;
        $primaria = Modalidad::where('nombre', 'Primaria Regular')->first()->id;
        $secundaria = Modalidad::where('nombre', 'Secundaria Regular')->first()->id;

        $grados = [
            ['nombre' => 'I Nivel', 'modalidad_id' => $preescolar],
            ['nombre' => 'II Nivel', 'modalidad_id' => $preescolar],
            ['nombre' => 'III Nivel', 'modalidad_id' => $preescolar],
            ['nombre' => '1ro', 'modalidad_id' => $primaria],
            ['nombre' => '2do', 'modalidad_id' => $primaria],
            ['nombre' => '3ro', 'modalidad_id' => $primaria],
            ['nombre' => '4to', 'modalidad_id' => $primaria],
            ['nombre' => '5to', 'modalidad_id' => $primaria],
            ['nombre' => '6to', 'modalidad_id' => $primaria],
            ['nombre' => '7mo', 'modalidad_id' => $secundaria],
            ['nombre' => '8vo', 'modalidad_id' => $secundaria],
            ['nombre' => '9no', 'modalidad_id' => $secundaria],
            ['nombre' => '10mo', 'modalidad_id' => $secundaria],
            ['nombre' => '11mo', 'modalidad_id' => $secundaria],
        ];

        foreach ($grados as $grado) { Grado::create($grado); }
    }
}