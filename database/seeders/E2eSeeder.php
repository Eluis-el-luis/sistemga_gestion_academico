<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Catálogos base del sistema para pruebas E2E.
 *
 * Se ejecuta UNA SOLA VEZ tras migrate:fresh (desde E2eScenarioSeeder, solo
 * si los catálogos están vacíos). Todo es idempotente (firstOrCreate/create).
 * NUNCA debe usarse contra la base de datos real.
 */
class E2eSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermisoSeeder::class,
            ModalidadSeeder::class,
            AnioEscolarSeeder::class,
            GradoSeeder::class,
            AsignaturaSeeder::class,
            GrupoMateriaSeeder::class,
            MallaCurricularSeeder::class,
            IndicadorLogroSeeder::class,
            CorteEvaluativoSeeder::class,
            BloqueHorarioSeeder::class,
            UsuarioSeeder::class,
        ]);
    }
}
