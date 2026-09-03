<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CorteEvaluativo;
use App\Models\AnioEscolar;

class CorteEvaluativoSeeder extends Seeder
{
    public function run(): void
    {
        $anio = AnioEscolar::where('nombre', '2026')->first()->id;

        $cortes = [
            ['numero' => 1, 'semestre' => 1, 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-04-15', 'peso_acumulado' => 60, 'peso_examen' => 40],
            ['numero' => 2, 'semestre' => 1, 'fecha_inicio' => '2026-04-16', 'fecha_fin' => '2026-06-30', 'peso_acumulado' => 60, 'peso_examen' => 40],
            ['numero' => 3, 'semestre' => 2, 'fecha_inicio' => '2026-07-15', 'fecha_fin' => '2026-09-15', 'peso_acumulado' => 60, 'peso_examen' => 40],
            ['numero' => 4, 'semestre' => 2, 'fecha_inicio' => '2026-09-16', 'fecha_fin' => '2026-11-30', 'peso_acumulado' => 60, 'peso_examen' => 40],
        ];

        foreach ($cortes as $corte) {
            CorteEvaluativo::updateOrCreate(
                ['anio_escolar_id' => $anio, 'numero' => $corte['numero']],
                $corte
            );
        }
    }
}
