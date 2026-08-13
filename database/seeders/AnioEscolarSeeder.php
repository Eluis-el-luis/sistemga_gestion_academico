<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AnioEscolar;
use Carbon\Carbon;

class AnioEscolarSeeder extends Seeder
{
    public function run(): void
    {
        // Configurando el año lectivo 2026 como el activo por defecto
        AnioEscolar::create([
            'nombre' => '2026',
            'fecha_inicio' => Carbon::create('2026', '02', '01')->toDateString(),
            'fecha_fin' => Carbon::create('2026', '11', '30')->toDateString(),
            'activo' => true,
        ]);
        
        // Puedes agregar un año futuro de prueba si lo deseas
        AnioEscolar::create([
            'nombre' => '2027',
            'fecha_inicio' => Carbon::create('2027', '02', '01')->toDateString(),
            'fecha_fin' => Carbon::create('2027', '11', '30')->toDateString(),
            'activo' => false,
        ]);
    }
}
