<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol; // Traemos de vuelta el modelo Rol para calmar a la base de datos

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Rescatamos los IDs de tu tabla nativa 'rol' para que PostgreSQL no se queje de valores nulos
        $rolDirector = Rol::firstOrCreate(['nombre' => 'Director'])->id;
        $rolSubdirector = Rol::firstOrCreate(['nombre' => 'Subdirector'])->id;
        $rolGestor = Rol::firstOrCreate(['nombre' => 'Gestor de Usuarios'])->id;

        // 1. DIRECTOR (Pilar 1)
        $director = Usuario::firstOrCreate(
            ['email' => 'martha.salinas@ccn.edu.ni'],
            [
                'nombre_completo' => 'Martha Sandra Salina Mendoza',
                'password' => Hash::make('password'),
                'activo' => true,
                'rol_id' => $rolDirector // <-- Le damos el rol_id que exige la migración
            ]
        );
        $director->assignRole('Director'); // Magia de Spatie para los permisos reales

        // 2. SUBDIRECTOR (Pilar 2)
        $subdirector = Usuario::firstOrCreate(
            ['email' => 'guillermina.matamoros@ccn.edu.ni'],
            [
                'nombre_completo' => 'Guillermina Matamoros',
                'password' => Hash::make('password'),
                'activo' => true,
                'rol_id' => $rolSubdirector // <-- Le damos el rol_id
            ]
        );
        $subdirector->assignRole('Subdirector');

        // 3. GESTOR DE USUARIOS (Pilar 3)
        $gestor = Usuario::firstOrCreate(
            ['email' => 'oswaldo.rivas@ccn.edu.ni'],
            [
                'nombre_completo' => 'Oswaldo Alberto Rivas Escobar',
                'password' => Hash::make('password'),
                'activo' => true,
                'rol_id' => $rolGestor // <-- Le damos el rol_id
            ]
        );
        $gestor->assignRole('Gestor de Usuarios');
    }
}