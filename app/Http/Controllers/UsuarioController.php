<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UsuarioController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $this->authorize('viewAny', Usuario::class);

            /** @var Usuario $authUser */
            $authUser = Auth::user();
            $query = Usuario::with('roles');
            $modalidades = Modalidad::orderBy('id')->get();

            if ($authUser->hasAnyRole(['gestor_it', 'Gestor de Usuarios']) && !$authUser->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
                $query->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['directora', 'subdirectora', 'Director', 'Subdirector']);
                });
            }

            if ($request->filled('modalidad')) {
                $modalidadId = (int) $request->input('modalidad');

                $query->whereHas('docente', function ($q) use ($modalidadId) {
                    $q->where('modalidad_id', $modalidadId)
                        ->orWhere('modalidad_coordina_id', $modalidadId)
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

            if ($request->filled('buscar')) {
                $termino = '%' . mb_strtolower(trim((string) $request->input('buscar')), 'UTF-8') . '%';

                $query->where(function ($q) use ($termino) {
                    $q->whereRaw('LOWER(nombre_completo) LIKE ?', [$termino])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$termino]);
                });
            }

            $usuarios = $query->orderBy('nombre_completo', 'asc')->paginate(15)->withQueryString();

            return view('academico.usuarios.index', compact('usuarios', 'modalidades'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el listado de usuarios.'));
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            $this->authorize('create', Usuario::class);

            /** @var Usuario $authUser */
            $authUser = Auth::user();
            $rolesExcluidos = $authUser->hasAnyRole(['secretaria', 'Secretaria']) ? [] : ['Alumno'];

            if (Usuario::role(['directora', 'Director'])->exists()) {
                array_push($rolesExcluidos, 'directora', 'Director');
            }

            if (Usuario::role(['subdirectora', 'Subdirector'])->exists()) {
                array_push($rolesExcluidos, 'subdirectora', 'Subdirector');
            }

            $roles = Role::whereNotIn('name', array_unique($rolesExcluidos))->get();
            $modalidades = Modalidad::orderBy('id')->get();

            return view('academico.usuarios.create', compact('roles', 'modalidades'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.usuarios.index')
                ->with('error', $this->formatearError($e, 'No fue posible abrir el formulario de registro de usuario.'));
        }
    }

    public function edit(Usuario $usuario): View|RedirectResponse
    {
        try {
            $this->authorize('update', $usuario);

            /** @var Usuario $authUser */
            $authUser = Auth::user();
            $rolesExcluidos = $authUser->hasAnyRole(['secretaria', 'Secretaria']) ? [] : ['Alumno'];

            if (Usuario::role(['directora', 'Director'])->where('id', '!=', $usuario->id)->exists()) {
                array_push($rolesExcluidos, 'directora', 'Director');
            }

            if (Usuario::role(['subdirectora', 'Subdirector'])->where('id', '!=', $usuario->id)->exists()) {
                array_push($rolesExcluidos, 'subdirectora', 'Subdirector');
            }

            $roles = Role::whereNotIn('name', array_unique($rolesExcluidos))->get();
            $modalidades = Modalidad::orderBy('id')->get();

            return view('academico.usuarios.edit', compact('usuario', 'roles', 'modalidades'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.usuarios.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar la información del usuario.'));
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:usuario,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array', 'min:1'],
            'codigo_unico_persona' => ['nullable', 'string', 'max:20'],
            'sexo' => ['nullable', 'in:M,F'],
            'modalidad_id' => ['nullable', 'integer', 'exists:modalidad,id'],
            'modalidad_coordina_id' => ['nullable', 'integer', 'exists:modalidad,id'],
        ]);

        $rolesSeleccionados = $validated['roles'];
        $esDirectora = count(array_intersect($rolesSeleccionados, ['directora', 'Director'])) > 0;
        $esSubdirectora = count(array_intersect($rolesSeleccionados, ['subdirectora', 'Subdirector'])) > 0;

        if ($esDirectora && Usuario::role(['directora', 'Director'])->exists()) {
            return back()->withInput()
                ->withErrors(['roles' => 'Ya existe una cuenta de Dirección asignada en el sistema.']);
        }

        if ($esSubdirectora && Usuario::role(['subdirectora', 'Subdirector'])->exists()) {
            return back()->withInput()
                ->withErrors(['roles' => 'Ya existe una cuenta de Subdirección asignada en el sistema.']);
        }

        $this->authorize('create', Usuario::class);

        try {
            DB::transaction(function () use ($validated) {
                $usuario = Usuario::create([
                    'nombre_completo' => $validated['nombre_completo'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'activo' => true,
                ]);

                $usuario->assignRole($validated['roles']);

                $esDocente = collect($validated['roles'])->contains(function ($rol) {
                    $lower = mb_strtolower((string) $rol, 'UTF-8');

                    return str_contains($lower, 'docente') || str_contains($lower, 'coordinador');
                });

                if ($esDocente) {
                    $esCoordinador = count(array_intersect($validated['roles'], ['coordinador', 'Coordinador'])) > 0;

                    Docente::create([
                        'usuario_id' => $usuario->id,
                        'codigo_unico_persona' => $validated['codigo_unico_persona'] ?? ('DOC-' . str_pad((string) $usuario->id, 4, '0', STR_PAD_LEFT)),
                        'sexo' => $validated['sexo'] ?? 'M',
                        'modalidad_id' => $validated['modalidad_id'] ?? null,
                        'es_coordinador' => $esCoordinador,
                        'modalidad_coordina_id' => $esCoordinador ? ($validated['modalidad_coordina_id'] ?? null) : null,
                    ]);
                }
            });

            return redirect()->route('academico.usuarios.index')
                ->with('success', 'Personal registrado y accesos configurados correctamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $this->formatearError($e, 'No fue posible registrar al usuario.'));
        }
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:usuario,email,' . $usuario->id],
            'activo' => ['required', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'codigo_unico_persona' => ['nullable', 'string', 'max:20'],
            'sexo' => ['nullable', 'in:M,F'],
            'modalidad_id' => ['nullable', 'integer', 'exists:modalidad,id'],
            'modalidad_coordina_id' => ['nullable', 'integer', 'exists:modalidad,id'],
        ]);

        $rolesSeleccionados = $validated['roles'];
        $esDirectora = count(array_intersect($rolesSeleccionados, ['directora', 'Director'])) > 0;
        $esSubdirectora = count(array_intersect($rolesSeleccionados, ['subdirectora', 'Subdirector'])) > 0;

        if ($esDirectora && Usuario::role(['directora', 'Director'])->where('id', '!=', $usuario->id)->exists()) {
            return back()->withInput()
                ->withErrors(['roles' => 'Ya existe otra cuenta de Dirección asignada en el sistema.']);
        }

        if ($esSubdirectora && Usuario::role(['subdirectora', 'Subdirector'])->where('id', '!=', $usuario->id)->exists()) {
            return back()->withInput()
                ->withErrors(['roles' => 'Ya existe otra cuenta de Subdirección asignada en el sistema.']);
        }

        $this->authorize('update', $usuario);

        try {
            DB::transaction(function () use ($validated, $usuario) {
                $usuario->update([
                    'nombre_completo' => $validated['nombre_completo'],
                    'email' => $validated['email'],
                    'activo' => (bool) $validated['activo'],
                ]);

                $usuario->syncRoles($validated['roles']);

                $tienePerfilPersona = collect($validated['roles'])->contains(function ($rol) {
                    $lower = mb_strtolower((string) $rol, 'UTF-8');

                    return str_contains($lower, 'docente')
                        || str_contains($lower, 'coordinador')
                        || str_contains($lower, 'director')
                        || str_contains($lower, 'subdirector')
                        || str_contains($lower, 'gestor')
                        || str_contains($lower, 'psicolog');
                }) || $usuario->docente !== null;

                if ($tienePerfilPersona) {
                    $esCoordinador = count(array_intersect($validated['roles'], ['coordinador', 'Coordinador'])) > 0;
                    $esDocente = collect($validated['roles'])->contains(function ($rol) {
                        return str_contains(mb_strtolower((string) $rol, 'UTF-8'), 'docente');
                    });

                    $codigoLimpio = isset($validated['codigo_unico_persona']) && trim((string) $validated['codigo_unico_persona']) !== ''
                        ? trim((string) $validated['codigo_unico_persona'])
                        : ($usuario->docente?->codigo_unico_persona ?? ('PER-' . str_pad((string) $usuario->id, 4, '0', STR_PAD_LEFT)));

                    $sexoLimpio = $validated['sexo'] ?? $usuario->docente?->sexo ?? 'M';

                    Docente::updateOrCreate(
                        ['usuario_id' => $usuario->id],
                        [
                            'codigo_unico_persona' => $codigoLimpio,
                            'sexo' => $sexoLimpio,
                            'modalidad_id' => $esDocente ? ($validated['modalidad_id'] ?? null) : ($usuario->docente?->modalidad_id ?? null),
                            'es_coordinador' => $esCoordinador,
                            'modalidad_coordina_id' => $esCoordinador ? ($validated['modalidad_coordina_id'] ?? null) : null,
                        ]
                    );
                }
            });

            return redirect()->route('academico.usuarios.index')
                ->with('success', 'Perfil y accesos actualizados correctamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $this->formatearError($e, 'No fue posible actualizar la información del usuario.'));
        }
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        try {
            $this->authorize('delete', $usuario);

            $usuario->update(['activo' => !$usuario->activo]);
            $mensaje = $usuario->activo ? 'Usuario reactivado en el sistema.' : 'Usuario desactivado por seguridad.';

            return redirect()->route('academico.usuarios.index')->with('success', $mensaje);
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible cambiar el estado de activación del usuario.'));
        }
    }

    public function resetPassword(Request $request, Usuario $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        try {
            $this->authorize('update', $usuario);

            $usuario->update([
                'password' => Hash::make($validated['password']),
            ]);

            return redirect()->route('academico.usuarios.index')
                ->with('success', 'Contraseña de ' . ($usuario->nombre_completo ?? $usuario->name) . ' restablecida exitosamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible restablecer la contraseña.'));
        }
    }
}