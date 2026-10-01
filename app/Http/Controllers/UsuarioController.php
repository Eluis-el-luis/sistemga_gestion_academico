<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
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
        try {
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

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla alguna relación o la consulta de filtrado
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al cargar el listado de usuarios del sistema.');
        }
    }

    public function create()
    {
        try {
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

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla al cargar el formulario de creación
            return redirect()->route('academico.usuarios.index')->with('error', 'No se pudo abrir el formulario de registro de usuario.');
        }
    }

    public function edit(Usuario $usuario)
    {
        try {
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

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla al cargar la edición
            return redirect()->route('academico.usuarios.index')->with('error', 'No se pudo cargar la información del usuario para su edición.');
        }
    }

    public function store(Request $request)
    {
        // Validación AFUERA del try-catch para proteger las alertas del formulario
        $request->validate([
            'nombre_completo' => 'required|string|max:120',
            'email'           => 'required|email|unique:usuario,email',
            'password'        => 'required|string|min:8',
            'roles'           => 'required|array|min:1',
            'codigo_unico_persona'  => 'nullable|string|max:20',
            'sexo'            => 'nullable|in:M,F',
            'modalidad_id'          => 'nullable|exists:modalidad,id',
            'modalidad_coordina_id' => 'nullable|exists:modalidad,id'
        ]);

        $rolesSeleccionados = $request->roles ?? [];
        $esDirector = in_array('Director', $rolesSeleccionados);
        $esSubdirector = in_array('Subdirector', $rolesSeleccionados);

        if ($esDirector && \App\Models\Usuario::role('Director')->exists()) {
            return back()->withInput()->withErrors(['roles' => 'Ya existe una cuenta de Dirección asignada en el sistema.']);
        }

        if ($esSubdirector && \App\Models\Usuario::role('Subdirector')->exists()) {
            return back()->withInput()->withErrors(['roles' => 'Ya existe una cuenta de Subdirección asignada en el sistema.']);
        }

        DB::beginTransaction();
        try {
            $this->authorize('create', Usuario::class);

            // Creamos el usuario
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
                    'modalidad_id'          => $request->modalidad_id,
                    'es_coordinador'        => collect($request->roles)->contains('Coordinador'),
                    'modalidad_coordina_id' => collect($request->roles)->contains('Coordinador') ? $request->modalidad_coordina_id : null,
                ]);
            }

            DB::commit();
            return redirect()->route('academico.usuarios.index')
                             ->with('success', 'Personal registrado y accesos configurados correctamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            DB::rollBack();
            // CONTINGENCIA: Si falla la inserción en la base de datos
            return back()->withInput()->with('error', 'Ocurrió un error técnico al registrar al usuario. El proceso fue cancelado por seguridad.');
        }
    }

    public function update(Request $request, Usuario $usuario)
    {
        // Validación AFUERA
        $request->validate([
            'nombre_completo' => 'required|string|max:120',
            'email'           => 'required|email|unique:usuario,email,' . $usuario->id,
            'activo'          => 'required|boolean',
            'roles'           => 'required|array|min:1',
            'codigo_unico_persona'  => 'nullable|string|max:20',
            'sexo'            => 'nullable|in:M,F',
            'modalidad_id'          => 'nullable|exists:modalidad,id',
            'modalidad_coordina_id' => 'nullable|exists:modalidad,id'
        ]);

        $rolesSeleccionados = $request->roles ?? [];
        $esDirector = in_array('Director', $rolesSeleccionados);
        $esSubdirector = in_array('Subdirector', $rolesSeleccionados);

        if ($esDirector && \App\Models\Usuario::role('Director')->where('id', '!=', $usuario->id)->exists()) {
            return back()->withInput()->withErrors(['roles' => 'Ya existe otra cuenta de Dirección asignada en el sistema.']);
        }

        if ($esSubdirector && \App\Models\Usuario::role('Subdirector')->where('id', '!=', $usuario->id)->exists()) {
            return back()->withInput()->withErrors(['roles' => 'Ya existe otra cuenta de Subdirección asignada en el sistema.']);
        }

        DB::beginTransaction();
        try {
            $this->authorize('update', $usuario);

            // Actualizamos únicamente los datos propios del usuario
            $usuario->update([
                'nombre_completo' => $request->nombre_completo,
                'email'           => $request->email,
                'activo'          => $request->activo,
            ]);

            // Spatie se encarga de guardar la relación del rol
            $usuario->syncRoles($request->roles);

            $esDocente = collect($request->roles)->contains(function ($rol) {
                return str_contains($rol, 'Docente') || $rol === 'Coordinador';
            });

            if ($esDocente) {
                \App\Models\Docente::updateOrCreate(
                    ['usuario_id' => $usuario->id],
                    [
                        'codigo_unico_persona'  => $request->codigo_unico_persona ?? $usuario->docente?->codigo_unico_persona ?? 'DOC-' . str_pad($usuario->id, 4, '0', STR_PAD_LEFT),
                        'sexo'                  => $request->sexo ?? $usuario->docente?->sexo ?? 'M',
                        'modalidad_id'          => $request->modalidad_id,
                        'es_coordinador'        => collect($request->roles)->contains('Coordinador'),
                        'modalidad_coordina_id' => collect($request->roles)->contains('Coordinador') ? $request->modalidad_coordina_id : null,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('academico.usuarios.index')
                             ->with('success', 'Perfil y accesos actualizados correctamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            DB::rollBack();
            // CONTINGENCIA: Si falla el update
            return back()->withInput()->with('error', 'Ocurrió un problema técnico al actualizar la información del usuario.');
        }
    }

    public function destroy(Usuario $usuario)
    {
        try {
            $this->authorize('delete', $usuario);

            $usuario->update(['activo' => !$usuario->activo]);
            $mensaje = $usuario->activo ? 'Usuario reactivado en el sistema.' : 'Usuario desactivado por seguridad.';

            return redirect()->route('academico.usuarios.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si la base de datos bloquea el cambio de estado
            return back()->with('error', 'No se pudo cambiar el estado de activación del usuario.');
        }
    }

    public function resetPassword(Request $request, Usuario $usuario)
    {
        // Validación AFUERA
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        try {
            $this->authorize('update', $usuario);

            $usuario->update([
                'password' => bcrypt($request->password)
            ]);

            return redirect()->route('academico.usuarios.index')
                             ->with('success', 'Contraseña de ' . ($usuario->nombre_completo ?? $usuario->name) . ' restablecida exitosamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla el reseteo de contraseña
            return back()->with('error', 'No se pudo restablecer la contraseña debido a un error del servidor.');
        }
    }
}