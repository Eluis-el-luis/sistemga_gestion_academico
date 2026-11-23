<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumnoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Alumno::class);

        try {
            $query = Alumno::with(['matriculas' => function ($q) {
                $q->latest('fecha_matricula');
            }, 'matriculas.aula.grado', 'matriculas.aula.modalidad']);

            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $query = $this->applyAlumnoScope($query, $usuario);

            if ($request->filled('buscar')) {
                $busqueda = trim((string) $request->buscar);
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre_completo', 'like', "%{$busqueda}%")
                        ->orWhere('codigo_unico_persona', 'like', "%{$busqueda}%");
                });
            }

            if ($request->filled('modalidad_id')) {
                $query->whereHas('matriculas.aula', fn ($q) => $q->where('modalidad_id', $request->modalidad_id));
            }

            if ($request->filled('grado_id')) {
                $query->whereHas('matriculas.aula', fn ($q) => $q->where('grado_id', $request->grado_id));
            }

            if ($request->filled('aula_id')) {
                $query->whereHas('matriculas', fn ($q) => $q->where('aula_id', $request->aula_id));
            }

            $estadoFiltro = $request->get(
                'estado',
                !$request->hasAny(['buscar', 'modalidad_id', 'grado_id', 'aula_id', 'estado']) ? 'activo' : null
            );

            if (!empty($estadoFiltro)) {
                $query->whereHas('matriculas', fn ($q) => $q->where('estado', $estadoFiltro));
            }

            $alumnos = $query->orderBy('nombre_completo', 'asc')
                ->paginate(15)
                ->withQueryString();

            $modalidades = Modalidad::all();
            $grados = Grado::all();
            $aulas = Aula::all();

            return view('academico.alumnos.index', compact('alumnos', 'modalidades', 'grados', 'aulas'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'No pudimos cargar el listado de alumnos.'));
        }
    }

    public function create()
    {
        $this->authorize('create', Alumno::class);

        try {
            return view('academico.alumnos.create');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.alumnos.index')->with('error', $this->formatearError($e, 'No se pudo abrir el formulario de registro.'));
        }
    }

    public function store(StoreAlumnoRequest $request)
    {
        $this->authorize('create', Alumno::class);
        $datosValidados = $request->validated();

        try {
            Alumno::create($datosValidados);

            return redirect()->route('academico.alumnos.index')
                ->with('success', 'Expediente del alumno registrado con éxito.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un error inesperado al intentar guardar el expediente.'));
        }
    }

    public function show(Alumno $alumno)
    {
        $this->authorize('viewAny', Alumno::class);

        try {
            $alumno->load(['matriculas.aula.grado', 'matriculas.anioEscolar']);

            return view('academico.alumnos.show', compact('alumno'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.alumnos.index')->with('error', $this->formatearError($e, 'No pudimos cargar los detalles de este alumno.'));
        }
    }

    public function edit(Alumno $alumno)
    {
        $this->authorize('update', $alumno);

        try {
            return view('academico.alumnos.edit', compact('alumno'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.alumnos.index')->with('error', $this->formatearError($e, 'No se pudo abrir el formulario de edición para este alumno.'));
        }
    }

    public function update(UpdateAlumnoRequest $request, Alumno $alumno)
    {
        $this->authorize('update', $alumno);
        $datosValidados = $request->validated();

        try {
            $alumno->update($datosValidados);

            return redirect()->route('academico.alumnos.index')
                ->with('success', 'Datos del alumno actualizados correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un problema al actualizar los datos del alumno.'));
        }
    }

    public function destroy(Alumno $alumno)
    {
        $this->authorize('delete', $alumno);

        try {
            $alumno->delete();

            return redirect()->route('academico.alumnos.index')
                ->with('success', 'Alumno eliminado del sistema.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se puede eliminar este alumno porque tiene registros asociados.'));
        }
    }

    public function misAlumnos()
    {
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $docente = Docente::where('usuario_id', $usuario->id)->firstOrFail();

            $aula = Aula::with('grado')->where('docente_guia_id', $docente->id)->first();
            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como Maestro Guía.');
            }

            $alumnos = Alumno::whereHas('matriculas', fn ($q) => $q->where('aula_id', $aula->id)->where('estado', 'activo'))->get();

            return view('academico.alumnos.mis-alumnos', compact('aula', 'alumnos'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Hubo un problema al intentar cargar tu lista de alumnos.'));
        }
    }

    protected function applyAlumnoScope($query, Usuario $usuario)
    {
        if ($usuario->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'secretaria', 'psicologa', 'Director', 'Subdirector', 'Gestor de Usuarios', 'Secretaria'])) {
            return $query;
        }

        $docente = $usuario->docente;
        if (!$docente) {
            return $query;
        }

        if ($usuario->hasAnyRole(['coordinador', 'Coordinador']) && $docente->es_coordinador && $docente->modalidad_coordina_id) {
            return $query->whereHas('matriculas.aula', fn ($q) => $q->where('modalidad_id', $docente->modalidad_coordina_id));
        }

        if ($usuario->hasAnyRole(['docente_guia', 'Docente Guia'])) {
            return $query->whereHas('matriculas', function ($q) use ($docente) {
                $q->whereIn('aula_id', Aula::where('docente_guia_id', $docente->id)->select('id'))
                    ->where('estado', 'activo');
            });
        }

        if ($usuario->hasAnyRole(['docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'Docente por Asignatura'])) {
            return $query->whereHas('matriculas.aula.asignaciones', function ($q) use ($docente) {
                $q->where('docente_id', $docente->id)->where('activo', true);
            });
        }

        return $query;
    }
}