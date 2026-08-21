<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Usuario::class);
        
        $query = Usuario::with('roles');

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where('nombre_completo', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%");
        }
        
        $usuarios = $query->paginate(15);
        
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
            'roles' => 'required|array|min:1' // Validamos que elijan al menos un rol
        ]);

        $rolesSeleccionados = $request->roles ?? [];
        $esDirector = in_array('Director', $rolesSeleccionados);
        $esSubdirector = in_array('Subdirector', $rolesSeleccionados);

        // Regla: No pueden existir dos roles de Dirección/Subdirección
        if ($esDirector && \App\Models\Usuario::role('Director')->exists()) {
            return back()->withErrors(['roles' => 'Ya existe una cuenta de Dirección. No pueden existir dos.'])->withInput();
        }

        if ($esSubdirector && \App\Models\Usuario::role('Subdirector')->exists()) {
            return back()->withErrors(['roles' => 'Ya existe una cuenta de Subdirección. No pueden existir dos.'])->withInput();
        }

        // Buscamos el rol primario por si cambió
        $rolPrincipal = Rol::where('nombre', $request->roles[0])->first();

        // 2. Crear el usuario en la BD
        $usuario = Usuario::create([
            'nombre_completo' => $request->nombre_completo,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $rolPrincipal ? $rolPrincipal->id : 1,
            'activo' => true
        ]);

        // 3. Sincronizar Spatie (Aquí ocurre la magia multi-rol)
        $usuario->syncRoles($request->roles);

        // 4. ¿Es un docente? Verificamos si en sus roles elegidos está la palabra "Docente"
        $esDocente = collect($request->roles)->contains(function ($rol) {
            return str_contains($rol, 'Docente');
        });

        if ($esDocente) {
            // Generamos su expediente si no existe
            \App\Models\Docente::firstOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'codigo_unico_persona' => 'DOC-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT),
                    'es_coordinador' => collect($request->roles)->contains('Coordinador'),
                ]
            );
        }

        return redirect()->route('academico.usuarios.index')
                         ->with('success', 'Usuario creado exitosamente con sus accesos.');
    }

    public function edit(Usuario $usuario)
    {
        $this->authorize('update', $usuario);
        
        $roles = Rol::all();
        
        return view('academico.usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $this->authorize('update', $usuario);

        $request->validate([
            'nombre_completo' => 'required|string|max:120',
            'email' => 'required|email|unique:usuario,email,' . $usuario->id,
            'activo' => 'required|boolean',
            'roles' => 'required|array|min:1'
        ]);

        $rolesSeleccionados = $request->roles ?? [];
        $esDirector = in_array('Director', $rolesSeleccionados);
        $esSubdirector = in_array('Subdirector', $rolesSeleccionados);

        if ($esDirector && \App\Models\Usuario::role('Director')->where('id', '!=', $usuario->id)->exists()) {
            return back()->withErrors(['roles' => 'Ya existe otra cuenta de Dirección asignada en el sistema.']);
        }

        if ($esSubdirector && \App\Models\Usuario::role('Subdirector')->where('id', '!=', $usuario->id)->exists()) {
            return back()->withErrors(['roles' => 'Ya existe otra cuenta de Subdirección asignada en el sistema.']);
        }

        // Buscamos el rol primario por si cambió
        $rolPrincipal = Rol::where('nombre', $request->roles[0])->first();

        $usuario->update([
            'nombre_completo' => $request->nombre_completo,
            'email' => $request->email,
            'rol_id' => $rolPrincipal ? $rolPrincipal->id : $usuario->rol_id,
            'activo' => $request->activo
        ]);

        // Magia Spatie: Actualiza automáticamente todos los roles (quita los viejos y pone los nuevos)
        $usuario->syncRoles($request->roles);

        // Actualizamos si es coordinador
        $esDocente = collect($request->roles)->contains(function ($rol) { return str_contains($rol, 'Docente'); });
        
        if ($esDocente) {
            \App\Models\Docente::updateOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'codigo_unico_persona' => 'DOC-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT),
                    'es_coordinador' => collect($request->roles)->contains('Coordinador'),
                ]
            );
        }

        return redirect()->route('academico.usuarios.index')
                         ->with('success', 'Perfil y accesos actualizados correctamente.');
    }

    public function destroy(Usuario $usuario)
    {
        $this->authorize('delete', $usuario);

        // Soft delete lógico cambiando estado
        $usuario->update(['activo' => !$usuario->activo]);
        $mensaje = $usuario->activo ? 'Usuario reactivado en el sistema.' : 'Usuario desactivado por seguridad.';

        return redirect()->route('academico.usuarios.index')->with('success', $mensaje);
    }

    /**
     * Restablece la contraseña desde el Modal (Autogenerada o Manual)
     */
    public function resetPassword(\Illuminate\Http\Request $request, Usuario $usuario)
    {
        // Reutilizamos la política de edición: 
        // Si no puede editar al usuario (ej. Subdirector intentando afectar al Director), bloquea la acción.
        $this->authorize('update', $usuario);

        // Validamos que venga una contraseña y cumpla el mínimo de seguridad
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        // Actualizamos encriptando la nueva clave
        $usuario->update([
            'password' => bcrypt($request->password)
        ]);

        return redirect()->route('academico.usuarios.index')
                         ->with('success', 'Contraseña de ' . ($usuario->nombre_completo ?? $usuario->name) . ' restablecida exitosamente.');
    }
}