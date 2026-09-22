<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DIRECTOR (Pilar 1)
        $director = Usuario::firstOrCreate(
            ['email' => 'martha.salinas@ccn.edu.ni'],
            [
                'nombre_completo' => 'Martha Sandra Salina Mendoza',
                'password' => Hash::make('password'),
                'activo' => true,
            ]
        );
        $director->assignRole('Director');

        // 2. SUBDIRECTOR (Pilar 2)
        $subdirector = Usuario::firstOrCreate(
            ['email' => 'guillermina.matamoros@ccn.edu.ni'],
            [
                'nombre_completo' => 'Guillermina Matamoros',
                'password' => Hash::make('password'),
                'activo' => true,
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
            ]
        );
        $gestor->assignRole('Gestor de Usuarios');
    }
}