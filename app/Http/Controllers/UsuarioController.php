<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol; // <-- IMPORTANTE: Traemos el modelo nativo Rol
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Usuario::class);
        
        $query = Usuario::with('roles');
        $authUser = auth()->user();
        
        // 1. Cargar modalidades para enviarlas al select de la vista
        $modalidades = \App\Models\Modalidad::orderBy('id')->get();

        // 2. FILTRO DE JERARQUÍA (Ocultar jefes al Gestor)
        if ($authUser->hasRole('Gestor de Usuarios') && !$authUser->hasAnyRole(['Director', 'Subdirector'])) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Director', 'Subdirector']);
            });
        }

        // 3. NUEVO FILTRO: Agrupar por Modalidad (incluye Modalidad Base)
        if ($request->filled('modalidad')) {
            $modalidadId = $request->modalidad;
            
            $query->whereHas('docente', function ($q) use ($modalidadId) {
                $q->where('modalidad_id', $modalidadId) // Filtra por Modalidad Base
                  ->orWhere('modalidad_coordina_id', $modalidadId) // Filtra si es coordinador
                  // O si imparte clases en algún aula de esa modalidad
                  ->orWhereExists(function ($subquery) use ($modalidadId) {
                      $subquery->select(DB::raw(1))
                               ->from('aula_asignatura_docente')
                               ->join('aula', 'aula_asignatura_docente.aula_id', '=', 'aula.id')
                               ->whereColumn('aula_asignatura_docente.docente_id', 'docente.id')
                               ->where('aula.modalidad_id', $modalidadId)
                               ->where('aula_asignatura_docente.activo', true);
                  });
            });
        }

        // 4. BUSCADOR BLINDADO
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            
            // Agrupamos la búsqueda en un closure para no romper las reglas anteriores
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre_completo', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%");
            });
        }
        
        // Se añade withQueryString() para que al cambiar de página no se pierda el filtro aplicado
        $usuarios = $query->orderBy('nombre_completo', 'asc')->paginate(15)->withQueryString();
        
        return view('academico.usuarios.index', compact('usuarios', 'modalidades'));
    }

    public function create()
    {
        $this->authorize('create', Usuario::class);

        // 1. Ocultamos 'Alumno' por defecto en el panel de personal
        $rolesExcluidos = ['Alumno']; 
        
        // 2. Si ya hay Director, ocultamos la casilla
        if (\App\Models\Usuario::role('Director')->exists()) {
            $rolesExcluidos[] = 'Director';
        }
        
        // 3. Si ya hay Subdirector, ocultamos la casilla
        if (\App\Models\Usuario::role('Subdirector')->exists()) {
            $rolesExcluidos[] = 'Subdirector';
        }

        // Consultamos a Spatie trayendo solo los roles permitidos
        $roles = \Spatie\Permission\Models\Role::whereNotIn('name', $rolesExcluidos)->get();
        
        // Cargar las modalidades para el selector de Modalidad Base
        $modalidades = \App\Models\Modalidad::orderBy('id')->get();

        return view('academico.usuarios.create', compact('roles', 'modalidades'));
    }

    public function edit(Usuario $usuario)
    {
        $this->authorize('update', $usuario);

        $rolesExcluidos = ['Alumno'];
        
        // En la edición, ocultamos el rol SOLO si está ocupado por OTRA persona.
        if (\App\Models\Usuario::role('Director')->where('id', '!=', $usuario->id)->exists()) {
            $rolesExcluidos[] = 'Director';
        }
        
        if (\App\Models\Usuario::role('Subdirector')->where('id', '!=', $usuario->id)->exists()) {
            $rolesExcluidos[] = 'Subdirector';
        }

        $roles = \Spatie\Permission\Models\Role::whereNotIn('name', $rolesExcluidos)->get();
        
        // Cargar las modalidades para el selector de Modalidad Base
        $modalidades = \App\Models\Modalidad::orderBy('id')->get();

        return view('academico.usuarios.edit', compact('usuario', 'roles', 'modalidades'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Usuario::class);

        $request->validate([
            'nombre_completo' => 'required|string|max:120',
            'email'           => 'required|email|unique:usuario,email',
            'password'        => 'required|string|min:8',
            'roles'           => 'required|array|min:1',
            'codigo_unico_persona'  => 'nullable|string|max:20',
            'sexo'            => 'nullable|in:M,F',
            'modalidad_id'          => 'nullable|exists:modalidad,id', // <-- Validación nueva
            'modalidad_coordina_id' => 'nullable|exists:modalidad,id'
        ]);

        $rolesSeleccionados = $request->roles ?? [];
        $esDirector = in_array('Director', $rolesSeleccionados);
        $esSubdirector = in_array('Subdirector', $rolesSeleccionados);

        if ($esDirector && \App\Models\Usuario::role('Director')->exists()) {
            return back()->withErrors(['roles' => 'Ya existe una cuenta de Dirección asignada en el sistema.']);
        }

        if ($esSubdirector && \App\Models\Usuario::role('Subdirector')->exists()) {
            return back()->withErrors(['roles' => 'Ya existe una cuenta de Subdirección asignada en el sistema.']);
        }

        // Creamos el usuario sin la columna fantasma rol_id
        $usuario = Usuario::create([
            'nombre_completo' => $request->nombre_completo,
            'email'           => $request->email,
            'password'        => bcrypt($request->password), 
            'activo'          => true,
        ]);

        // Spatie asigna los permisos correctamente en su tabla intermedia
        $usuario->assignRole($request->roles);

        $esDocente = collect($request->roles)->contains(function ($rol) {
            return str_contains($rol, 'Docente') || $rol === 'Coordinador';
        });

        if ($esDocente) {
            \App\Models\Docente::create([
                'usuario_id'            => $usuario->id,
                'codigo_unico_persona'  => $request->codigo_unico_persona ?? 'DOC-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT),
                'sexo'                  => $request->sexo ?? 'M',
                'modalidad_id'          => $request->modalidad_id, // <-- Guardar modalidad base
                'es_coordinador'        => collect($request->roles)->contains('Coordinador'),
                'modalidad_coordina_id' => collect($request->roles)->contains('Coordinador') ? $request->modalidad_coordina_id : null,
            ]);
        }

        return redirect()->route('academico.usuarios.index')
                         ->with('success', 'Personal registrado y accesos configurados correctamente.');
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
            'modalidad_id'          => 'nullable|exists:modalidad,id', // <-- Validación nueva
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

        // Actualizamos únicamente los datos propios del usuario
        $usuario->update([
            'nombre_completo' => $request->nombre_completo,
            'email'           => $request->email,
            'activo'          => $request->activo,
        ]);

        // Spatie se encarga mágicamente de guardar la relación del rol en la base de datos
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
                    'modalidad_id'          => $request->modalidad_id, // <-- Guardar/Actualizar modalidad base
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