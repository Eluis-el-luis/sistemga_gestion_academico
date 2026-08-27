<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. CATÁLOGOS Y ESTRUCTURA (Se queda)
            RolSeeder::class,
            PermisoSeeder::class,
            ModalidadSeeder::class,
            AnioEscolarSeeder::class,
            GradoSeeder::class,
            AsignaturaSeeder::class,
            MallaCurricularSeeder::class, 
            IndicadorLogroSeeder::class,
            CorteEvaluativoSeeder::class,
            BloqueHorarioSeeder::class,

            // 2. CUENTAS ADMINISTRATIVAS
            UsuarioSeeder::class, 

        ]);
    }
}