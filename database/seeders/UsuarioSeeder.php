<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $rolDirector = Rol::where('nombre', 'Director')->first()->id;
        
        // Ajustamos la búsqueda al nuevo nombre del rol
        $rolDocente = Rol::where('nombre', 'Docente')->first()->id;

        // Usuario Director
        Usuario::create([
            'nombre_completo' => 'Admin Sistema',
            'email' => 'admin@colegio.edu.ni',
            'password' => Hash::make('password123'),
            'rol_id' => $rolDirector,
            'activo' => true
        ]);

        // Usuario Docente
        Usuario::create([
            'nombre_completo' => 'Juan Pérez',
            'email' => 'juan.perez@colegio.edu.ni',
            'password' => Hash::make('password123'),
            'rol_id' => $rolDocente,
            'activo' => true
        ]);
    }
}