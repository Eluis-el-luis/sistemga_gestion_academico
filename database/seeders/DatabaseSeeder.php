<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            ModalidadSeeder::class,
            AnioEscolarSeeder::class,
            GradoSeeder::class,           
            CorteEvaluativoSeeder::class, 
            AsignaturaSeeder::class,
            MallaCurricularSeeder::class,
            IndicadorLogroSeeder::class,
            UsuarioSeeder::class, 
            DocenteSeeder::class, 
            AlumnoSeeder::class,
            AulaSeeder::class,
            MatriculaSeeder::class,
            AulaAsignaturaDocenteSeeder::class,
            HorarioSeeder::class,
        ]);
    }
}
