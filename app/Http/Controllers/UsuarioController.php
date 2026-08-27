<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Spatie\Permission\Models\Role;
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
        
        // ¡Orden alfabético restaurado!
        $usuarios = $query->orderBy('nombre_completo', 'asc')->paginate(15);
        
        return view('academico.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $this->authorize('create', Usuario::class);
        
        $roles = Role::all();
        $modalidades = \App\Models\Modalidad::all();
        
        return view('academico.usuarios.create', compact('roles', 'modalidades'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Usuario::class);

        $request->validate([
            'nombre_completo' => 'required|string|max:120',
            'email'           => 'required|email|unique:usuario,email',
            'password'        => 'required|string|min:8',
            'roles'           => 'required|array|min:1',
            'codigo_unico_persona'  => 'nullable|string|max:20|unique:docente,codigo_unico_persona',
            'sexo'            => 'nullable|in:M,F',
            'modalidad_coordina_id' => 'nullable|exists:modalidad,id'
        ]);

        $rolesSeleccionados = $request->roles ?? [];
        $esDirector = in_array('Director', $rolesSeleccionados);
        $esSubdirector = in_array('Subdirector', $rolesSeleccionados);

        if ($esDirector && \App\Models\Usuario::role('Director')->exists()) {
            return back()->withErrors(['roles' => 'Ya existe una cuenta de Dirección. No pueden existir dos.'])->withInput();
        }

        if ($esSubdirector && \App\Models\Usuario::role('Subdirector')->exists()) {
            return back()->withErrors(['roles' => 'Ya existe una cuenta de Subdirección. No pueden existir dos.'])->withInput();
        }

        // Creación limpia sin rol_id
        $usuario = Usuario::create([
            'nombre_completo' => $request->nombre_completo,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'activo'          => true
        ]);

        // Spatie se encarga de la relación aquí
        $usuario->syncRoles($request->roles);

        $esDocente = collect($request->roles)->contains(function ($rol) {
            return str_contains($rol, 'Docente') || $rol === 'Coordinador';
        });

        if ($esDocente) {
            $esCoordinador = collect($request->roles)->contains('Coordinador');
            
            \App\Models\Docente::create([
                'usuario_id'            => $usuario->id,
                'codigo_unico_persona'  => $request->codigo_unico_persona ?? 'DOC-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT),
                'sexo'                  => $request->sexo ?? 'M',
                'es_coordinador'        => $esCoordinador,
                'modalidad_coordina_id' => $esCoordinador ? $request->modalidad_coordina_id : null,
            ]);
        }

        return redirect()->route('academico.usuarios.index')
                         ->with('success', 'Usuario creado exitosamente con sus accesos y perfil.');
    }

    public function edit(Usuario $usuario)
    {
        $this->authorize('update', $usuario);
        
        $roles = Role::all();
        $modalidades = \App\Models\Modalidad::all();
        
        return view('academico.usuarios.edit', compact('usuario', 'roles', 'modalidades'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $this->authorize('update', $usuario);

        $request->validate([
            'nombre_completo' => 'required|string|max:120',
            'email'           => 'required|email|unique:usuario,email,' . $usuario->id,
            'activo'          => 'required|boolean',
            'roles'           => 'required|array|min:1',
            'codigo_unico_persona'  => 'nullable|string|max:20',
            'sexo'            => 'nullable|in:M,F',
            'modalidad_coordina_id' => 'nullable|exists:modalidad,id'
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

        // Actualización limpia sin rol_id
        $usuario->update([
            'nombre_completo' => $request->nombre_completo,
            'email'           => $request->email,
            'activo'          => $request->activo
        ]);

        // Spatie se encarga de la actualización de roles
        $usuario->syncRoles($request->roles);

        $esDocente = collect($request->roles)->contains(function ($rol) {
            return str_contains($rol, 'Docente') || $rol === 'Coordinador';
        });

        if ($esDocente) {
            \App\Models\Docente::updateOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'codigo_unico_persona'  => $request->codigo_unico_persona ?? $usuario->docente->codigo_unico_persona ?? 'DOC-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT),
                    'sexo'                  => $request->sexo ?? $usuario->docente->sexo ?? 'M',
                    'es_coordinador'        => collect($request->roles)->contains('Coordinador'),
                    'modalidad_coordina_id' => collect($request->roles)->contains('Coordinador') ? $request->modalidad_coordina_id : null,
                ]
            );
        }

        return redirect()->route('academico.usuarios.index')
                         ->with('success', 'Perfil y accesos actualizados correctamente.');
    }

    public function destroy(Usuario $usuario)
    {
        $this->authorize('delete', $usuario);

        $usuario->update(['activo' => !$usuario->activo]);
        $mensaje = $usuario->activo ? 'Usuario reactivado en el sistema.' : 'Usuario desactivado por seguridad.';

        return redirect()->route('academico.usuarios.index')->with('success', $mensaje);
    }

    public function resetPassword(Request $request, Usuario $usuario)
    {
        $this->authorize('update', $usuario);

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $usuario->update([
            'password' => bcrypt($request->password)
        ]);

        return redirect()->route('academico.usuarios.index')
                         ->with('success', 'Contraseña de ' . ($usuario->nombre_completo ?? $usuario->name) . ' restablecida exitosamente.');
    }
}