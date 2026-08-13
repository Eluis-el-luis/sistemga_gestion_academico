<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Horario;
use App\Models\AulaAsignaturaDocente;

class HorarioSeeder extends Seeder
{
    public function run(): void
    {
        // Traemos la asignación que acabamos de crear (Matemática en 5to A)
        $asignacion = AulaAsignaturaDocente::first()->id;

        // Bloque de 45 minutos como indica el documento
        Horario::create([
            'aula_asignatura_docente_id' => $asignacion,
            'dia_semana' => 'Lunes',
            'hora_inicio' => '08:00:00',
            'hora_fin' => '08:45:00'
        ]);
    }
}
