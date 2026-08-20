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
        // 1. Obtenemos los IDs de tu tabla nativa 'roles' (Para la columna rol_id)
        // Usamos el operador nullsafe (?->id) por si algún rol no existe en tu tabla nativa, no rompa el código.
        // En lugar de ::where(...)->first()
        $rolDirector = Rol::firstOrCreate(['nombre' => 'Director'])->id;
        $rolSubdirector = Rol::firstOrCreate(['nombre' => 'Subdirector'])->id;
        $rolCoordinador = Rol::firstOrCreate(['nombre' => 'Coordinador'])->id;
        $rolGestor = Rol::firstOrCreate(['nombre' => 'Gestor de Usuarios'])->id;
        $rolDocenteGuia = Rol::firstOrCreate(['nombre' => 'Docente Guía'])->id;
        $rolDocenteAsignatura = Rol::firstOrCreate(['nombre' => 'Docente por Asignatura'])->id;

        // 2. DIRECTOR (Administrador Global)
        $director = Usuario::create([
            'nombre_completo' => 'Admin Sistema',
            'email' => 'admin@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolDirector,
            'activo' => true,
        ]);
        $director->assignRole('Director'); // Rol Spatie

        // 3. SUB-DIRECTIVO (Guillermina)
        $subdirector = Usuario::create([
            'nombre_completo' => 'Guillermina',
            'email' => 'guillermina@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolSubdirector,
            'activo' => true,
        ]);
        $subdirector->assignRole('Subdirector');

        // 4. COORDINADOR MULTI-ROL (Duglas: Coordinador + Guía + Asignatura)
        $coordinador = Usuario::create([
            'nombre_completo' => 'Duglas',
            'email' => 'duglas@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolCoordinador, // Rol principal
            'activo' => true,
        ]);
        // ¡Magia de Spatie! Le asignamos los 3 roles de un solo golpe
        $coordinador->assignRole(['Coordinador', 'Docente Guía', 'Docente por Asignatura']);

        // 5. GESTOR MULTI-ROL (Oswaldo: Gestor + Guía + Asignatura)
        $gestor = Usuario::create([
            'nombre_completo' => 'Oswaldo',
            'email' => 'oswaldo@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolGestor, // Rol principal
            'activo' => true,
        ]);
        $gestor->assignRole(['Gestor de Usuarios', 'Docente Guía', 'Docente por Asignatura']);

        // 6. DOCENTE GUÍA (Scarleth)
        $guia = Usuario::create([
            'nombre_completo' => 'Scarleth',
            'email' => 'scarleth@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolDocenteGuia,
            'activo' => true,
        ]);
        $guia->assignRole('Docente Guía');

        // 7. DOCENTE ASIGNATURA (Joel)
        $asignatura = Usuario::create([
            'nombre_completo' => 'Joel',
            'email' => 'joel@colegio.edu.ni',
            'password' => bcrypt('password'),
            'rol_id' => $rolDocenteAsignatura,
            'activo' => true,
        ]);
        $asignatura->assignRole('Docente por Asignatura');
    }
}