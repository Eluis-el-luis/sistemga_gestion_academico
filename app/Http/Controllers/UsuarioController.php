<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UsuarioController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Usuario::class);
        
        // Traemos a los usuarios con su rol de la base de datos
        $usuarios = Usuario::with('rol')->paginate(15);
        
        return view('academico.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $this->authorize('create', Usuario::class);
        
        $roles = Rol::all();
        
        return view('academico.usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Usuario::class);

        $request->validate([
            'nombre_completo' => 'required|string|max:120',
            'email' => 'required|email|unique:usuario,email',
            'password' => 'required|string|min:8',
            'rol_id' => 'required|exists:rol,id'
        ]);

        // 1. Crear el usuario en la BD (Esto ya lo tienes)
        $usuario = Usuario::create([
            'nombre_completo' => $request->nombre_completo,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'rol_id' => $request->rol_id,
            'activo' => true
        ]);

        // 2. Sincronizar con Spatie mágicamente (Esto ya lo tienes)
        $rol = Rol::find($request->rol_id);
        if ($rol) {
            $usuario->assignRole($rol->nombre);

            // --- ¡NUEVA MAGIA AQUÍ! ---
            // 3. Si el rol es de un Docente, creamos su expediente automáticamente
            if (str_contains($rol->nombre, 'Docente')) {
                \App\Models\Docente::create([
                    'usuario_id' => $usuario->id,
                    // Generamos un código temporal automático (ej. DOC-0005)
                    'codigo_unico_persona' => 'DOC-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT),
                    'es_coordinador' => false,
                ]);
            }
        }

        return redirect()->route('academico.usuarios.index')
                         ->with('success', 'Usuario creado exitosamente.');
    }
}