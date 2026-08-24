<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buscamos los IDs de los DOCENTES (no de los usuarios) vinculando los correos
        $idScarleth = DB::table('docente')->where('usuario_id', Usuario::where('email', 'scarleth@colegio.edu.ni')->first()->id)->value('id');
        $idOswaldo  = DB::table('docente')->where('usuario_id', Usuario::where('email', 'oswaldo@colegio.edu.ni')->first()->id)->value('id');
        $idDuglas   = DB::table('docente')->where('usuario_id', Usuario::where('email', 'duglas@colegio.edu.ni')->first()->id)->value('id');

        // 2. Definimos las aulas usando los campos exactos de tu tabla
        $aulas = [
            // Preescolar (Guiada por Scarleth)
            [
                'nombre' => '3er Nivel',
                'grado_id' => 3,             // Asumiendo ID 3 para 3er nivel
                'modalidad_id' => 1,         // 1 = Preescolar
                'turno' => 'Matutino',
                'docente_guia_id' => $idScarleth,
                'anio_escolar_id' => 1,      // Asumiendo que el año lectivo activo es ID 1
                'cupo' => 25,
            ],
            // Primaria (Guiada por Oswaldo)
            [
                'nombre' => '5to Grado A',
                'grado_id' => 8,             // Asumiendo ID 8 para 5to grado
                'modalidad_id' => 2,         // 2 = Primaria
                'turno' => 'Matutino',
                'docente_guia_id' => $idOswaldo,
                'anio_escolar_id' => 1,
                'cupo' => 35,
            ],
            // Secundaria (Guiada por Duglas)
            [
                'nombre' => '11vo Grado A',
                'grado_id' => 14,            // Asumiendo ID 14 para 11vo grado
                'modalidad_id' => 3,         // 3 = Secundaria
                'turno' => 'Matutino',
                'docente_guia_id' => $idDuglas,
                'anio_escolar_id' => 1,
                'cupo' => 40,
            ],
        ];

        // 3. Insertamos en la BD
        foreach ($aulas as $aula) {
            DB::table('aula')->updateOrInsert(
                [
                    'nombre' => $aula['nombre'], 
                    'turno' => $aula['turno'], 
                    'anio_escolar_id' => $aula['anio_escolar_id']
                ], 
                $aula
            );
        }
    }
}