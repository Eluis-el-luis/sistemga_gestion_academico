<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AulaAsignaturaDocente;
use App\Models\Aula;
use App\Models\Asignatura;
use App\Models\Docente;
use App\Models\AnioEscolar;

class AulaAsignaturaDocenteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buscamos el Año Escolar
        $anioActual = AnioEscolar::where('activo', true)->first()->id;

        // 2. Buscamos las Aulas
        $aulaPreescolar = Aula::where('nombre', '3er Nivel')->first()->id;
        $aulaPrimaria   = Aula::where('nombre', '5to Grado A')->first()->id;
        $aulaSecundaria = Aula::where('nombre', '11vo Grado A')->first()->id;

        // 3. Buscamos los Docentes por su CUP
        $docenteDuglas   = Docente::where('codigo_unico_persona', 'CUP-DUGLAS-001')->first()->id;
        $docenteOswaldo  = Docente::where('codigo_unico_persona', 'CUP-OSWALDO-002')->first()->id;
        $docenteScarleth = Docente::where('codigo_unico_persona', 'CUP-SCARLETH-003')->first()->id;
        $docenteJoel     = Docente::where('codigo_unico_persona', 'CUP-JOEL-004')->first()->id;

        // 4. Asignaturas (Quitamos modalidad_id porque la tabla es un catálogo simple)
        $asignaturaMate       = Asignatura::firstOrCreate(['nombre' => 'Matemática'])->id;
        $asignaturaLengua     = Asignatura::firstOrCreate(['nombre' => 'Lengua y Literatura'])->id;
        $asignaturaDesarrollo = Asignatura::firstOrCreate(['nombre' => 'Desarrollo Infantil'])->id;


        // --- CREAMOS LAS ASIGNACIONES MANTENIENDO TU FORMATO ---

        // Scarleth da clase en Preescolar
        AulaAsignaturaDocente::create([
            'aula_id' => $aulaPreescolar,
            'asignatura_id' => $asignaturaDesarrollo,
            'docente_id' => $docenteScarleth,
            'anio_escolar_id' => $anioActual,
            'horas_semanales' => 10
        ]);

        // Joel da Matemática en Primaria
        AulaAsignaturaDocente::create([
            'aula_id' => $aulaPrimaria,
            'asignatura_id' => $asignaturaMate,
            'docente_id' => $docenteJoel,
            'anio_escolar_id' => $anioActual,
            'horas_semanales' => 5
        ]);

        // Joel también da Matemática en Secundaria
        AulaAsignaturaDocente::create([
            'aula_id' => $aulaSecundaria,
            'asignatura_id' => $asignaturaMate,
            'docente_id' => $docenteJoel,
            'anio_escolar_id' => $anioActual,
            'horas_semanales' => 5
        ]);

        // Oswaldo da Lengua y Literatura en Primaria
        AulaAsignaturaDocente::create([
            'aula_id' => $aulaPrimaria,
            'asignatura_id' => $asignaturaLengua,
            'docente_id' => $docenteOswaldo,
            'anio_escolar_id' => $anioActual,
            'horas_semanales' => 6
        ]);

        // Duglas da Lengua y Literatura en Secundaria
        AulaAsignaturaDocente::create([
            'aula_id' => $aulaSecundaria,
            'asignatura_id' => $asignaturaLengua,
            'docente_id' => $docenteDuglas,
            'anio_escolar_id' => $anioActual,
            'horas_semanales' => 5
        ]);
    }
}