<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Docente;
use App\Models\Usuario;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        // Buscamos el usuario docente que acabamos de crear
        $usuarioDOcenteAsig = Usuario::where('email', 'guia@colegio.edu.ni')->first();

        Docente::create([
            'usuario_id' => $usuarioDOcenteAsig->id,
            'codigo_unico_persona' => 'DOC-001',
            'sexo' => 'M',
            'es_coordinador' => false,
            'modalidad_coordina_id' => null
        ]);
    }
}
