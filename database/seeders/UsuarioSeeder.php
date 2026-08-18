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
        // 1. Obtenemos los IDs buscando por los nuevos nombres exactos
        $rolDirector = Rol::where('nombre', 'Director')->first()->id;
        $rolDocenteGuia = Rol::where('nombre', 'Docente Guía')->first()->id;
        $rolDocenteAsignatura = Rol::where('nombre', 'Docente por Asignatura')->first()->id;

        // 2. Creamos al Director
        $director = Usuario::create([
            'nombre_completo' => 'Admin Sistema',
            'email' => 'admin@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolDirector,
            'activo' => true,
        ]);
        // Le asignamos el permiso de Spatie
        $director->assignRole('Director');

        // 3. Creamos un Docente Guía
        $docenteGuia = Usuario::create([
            'nombre_completo' => 'Profesor Guía',
            'email' => 'guia@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolDocenteGuia,
            'activo' => true,
        ]);
        $docenteGuia->assignRole('Docente Guia');

        // 4. Creamos un Docente por Asignatura 
        $docenteAsignatura = Usuario::create([
            'nombre_completo' => 'Profesor Matemática',
            'email' => 'mate@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolDocenteAsignatura,
            'activo' => true,
        ]);
        $docenteAsignatura->assignRole('Docente por Asignatura');
    }
}