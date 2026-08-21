<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\AnioEscolar;

class MatriculaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buscamos las 3 aulas que creamos en AulaSeeder
        $aulaPreescolar = Aula::where('nombre', '3er Nivel')->first();
        $aulaPrimaria   = Aula::where('nombre', '5to Grado A')->first();
        $aulaSecundaria = Aula::where('nombre', '11vo Grado A')->first();

        // 2. Buscamos el año lectivo activo (Tu excelente lógica)
        $anioActual = AnioEscolar::where('activo', true)->first();
        $anioId = $anioActual ? $anioActual->id : 1; // Por si acaso no hay ninguno activo aún

        // 3. Tomamos los primeros 6 alumnos de la BD
        $alumnos = Alumno::take(6)->get();

        if ($alumnos->count() < 6) {
            $this->command->info('⚠️ Crea al menos 6 alumnos en tu base de datos para distribuir 2 en cada aula.');
            return;
        }

        // 4. Matriculamos a los alumnos distribuidos usando Eloquent (updateOrCreate evita duplicados)
        
        // --- PREESCOLAR (Scarleth) ---
        Matricula::updateOrCreate(
            ['alumno_id' => $alumnos[0]->id, 'anio_escolar_id' => $anioId],
            ['aula_id' => $aulaPreescolar->id, 'estado' => 'activo', 'fecha_matricula' => '2026-02-01']
        );
        Matricula::updateOrCreate(
            ['alumno_id' => $alumnos[1]->id, 'anio_escolar_id' => $anioId],
            ['aula_id' => $aulaPreescolar->id, 'estado' => 'retirado', 'fecha_matricula' => '2026-02-01', 'fecha_retiro' => '2026-05-15']
        );

        // --- PRIMARIA (Oswaldo) ---
        Matricula::updateOrCreate(
            ['alumno_id' => $alumnos[2]->id, 'anio_escolar_id' => $anioId],
            ['aula_id' => $aulaPrimaria->id, 'estado' => 'activo', 'fecha_matricula' => '2026-02-02']
        );
        Matricula::updateOrCreate(
            ['alumno_id' => $alumnos[3]->id, 'anio_escolar_id' => $anioId],
            ['aula_id' => $aulaPrimaria->id, 'estado' => 'repitente', 'fecha_matricula' => '2026-02-02']
        );

        // --- SECUNDARIA (Duglas) ---
        Matricula::updateOrCreate(
            ['alumno_id' => $alumnos[4]->id, 'anio_escolar_id' => $anioId],
            ['aula_id' => $aulaSecundaria->id, 'estado' => 'activo', 'fecha_matricula' => '2026-02-03']
        );
        Matricula::updateOrCreate(
            ['alumno_id' => $alumnos[5]->id, 'anio_escolar_id' => $anioId],
            ['aula_id' => $aulaSecundaria->id, 'estado' => 'activo', 'fecha_matricula' => '2026-02-03']
        );
    }
}
