<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buscamos a los usuarios que creamos en UsuarioSeeder
        $duglas = Usuario::where('email', 'duglas@colegio.edu.ni')->first();
        $oswaldo = Usuario::where('email', 'oswaldo@colegio.edu.ni')->first();
        $scarleth = Usuario::where('email', 'scarleth@colegio.edu.ni')->first();
        $joel = Usuario::where('email', 'joel@colegio.edu.ni')->first();

        // 2. Los registramos en la tabla 'docente' con tus campos exactos
        $docentes = [
            [
                'usuario_id' => $duglas->id,
                'codigo_unico_persona' => 'CUP-DUGLAS-001',
                'sexo' => 'M',
                'es_coordinador' => true,
                'modalidad_coordina_id' => 3, // Asumiendo que 3 es Secundaria
            ],
            [
                'usuario_id' => $oswaldo->id,
                'codigo_unico_persona' => 'CUP-OSWALDO-002',
                'sexo' => 'M',
                'es_coordinador' => false,
                'modalidad_coordina_id' => null,
            ],
            [
                'usuario_id' => $scarleth->id,
                'codigo_unico_persona' => 'CUP-SCARLETH-003',
                'sexo' => 'F',
                'es_coordinador' => false,
                'modalidad_coordina_id' => null,
            ],
            [
                'usuario_id' => $joel->id,
                'codigo_unico_persona' => 'CUP-JOEL-004',
                'sexo' => 'M',
                'es_coordinador' => false,
                'modalidad_coordina_id' => null,
            ],
        ];

        // 3. Insertamos evitando duplicados
        foreach ($docentes as $docente) {
            DB::table('docente')->updateOrInsert(
                ['usuario_id' => $docente['usuario_id']], 
                $docente
            );
        }
    }
}