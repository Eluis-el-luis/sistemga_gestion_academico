<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatriculaRequest;
use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\Matricula;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MatriculaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $this->authorize('viewAny', Matricula::class);

            /** @var Usuario $usuario */
            $usuario = Auth::user();$anioActivo = AnioEscolar::where('estado', 'activo')->first();
            $aniosEscolares = AnioEscolar::orderByDesc('fecha_inicio')->get();$query = Matricula::with(['alumno', 'aula.grado', 'aula.modalidad', 'anioEscolar']);

            if ($request->filled('anio_escolar_id')) {
                $query->where('anio_escolar_id', (int)$request->input('anio_escolar_id'));
            } elseif (!$request->has('anio_escolar_id') &&$anioActivo) {
                $query->where('anio_escolar_id',$anioActivo->id);
            }

            if ($request->filled('buscar')) {$termino = '%' . mb_strtolower(trim((string) $request->input('buscar')), 'UTF-8') . '\%';$query->whereHas('alumno', function ($q) use ($termino) {
                    $q->whereRaw('LOWER(nombre_completo) LIKE ?', [$termino])
                        ->orWhereRaw('LOWER(codigo_unico_persona) LIKE ?', [$termino]);
                });
            }

            if ($request->filled('aula_id')) {
                $query->where('aula_id', (int)$request->input('aula_id'));
            }

            if ($request->filled('estado')) {
                $query->where('estado', (string)$request->input('estado'));
            }

            $query = $this->applyMatriculaScope($query, $usuario);$matriculas = $query->latest('fecha_matricula')->paginate(15)->withQueryString();$aulas = $this->getAulasScope($usuario);

            return view('academico.matriculas.index', compact('matriculas', 'aniosEscolares', 'aulas', 'anioActivo'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el listado de matrículas.'));
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        try {
            $this->authorize('create', Matricula::class);

            $anioActivo = AnioEscolar::where('estado', 'activo')->first();

            $alumnos = Alumno::whereDoesntHave('matriculas', function ($q) use ($anioActivo) {
                if ($anioActivo) {
                    $q->where('anio_escolar_id',$anioActivo->id);
                }
            })->orderBy('nombre_completo')->get();

            $aulas = Aula::with(['grado', 'modalidad'])
                ->withCount(['matriculas' => function ($query) use ($anioActivo) {$query->where('estado', 'activo');
                    if ($anioActivo) {
                        $query->where('anio_escolar_id',$anioActivo->id);
                    }
                }])->get();

            $anios = AnioEscolar::where('estado', 'activo')->get();
            $alumnoSeleccionado =$request->query('alumno_id');

            return view('academico.matriculas.create', compact('alumnos', 'aulas', 'anios', 'alumnoSeleccionado'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.matriculas.index')
                ->with('error', $this->formatearError($e, 'No fue posible abrir el formulario de matrícula.'));
        }
    }

    public function store(StoreMatriculaRequest $request): RedirectResponse
    {
        $datosValidados =$request->validated();

        try {
            $this->authorize('create', Matricula::class);

            $aula = Aula::withCount(['matriculas' => function ($query) use ($datosValidados) {$query->where('estado', 'activo')
                    ->where('anio_escolar_id', $datosValidados['anio_escolar_id'] ?? null);
            }])->findOrFail($datosValidados['aula_id']);

            if ($aula->matriculas_count >=$aula->cupo) {
                return back()->withInput()
                    ->with('error', 'Esta aula ha alcanzado su cupo máximo (' . $aula->cupo . ').');
            }

            Matricula::create($datosValidados);

            return redirect()->route('academico.matriculas.create')
                ->with('success', '¡Estudiante matriculado exitosamente! Puedes registrar al siguiente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $this->formatearError($e, 'No fue posible procesar la matrícula.'));
        }
    }

    public function retirar(Request $request, Matricula$matricula): RedirectResponse
    {
        try {
            $this->authorize('update', $matricula);$matricula->update(['estado' => 'retirado', 'fecha_retiro' => now()->toDateString()]);

            return redirect()->route('academico.matriculas.index')
                ->with('success', 'Estudiante retirado correctamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible actualizar el estado de la matrícula a retirado.'));
        }
    }

    public function reactivar(Request $request, Matricula$matricula): RedirectResponse
    {
        try {
            $this->authorize('update', $matricula);$matricula->update(['estado' => 'activo', 'fecha_retiro' => null]);

            return redirect()->route('academico.matriculas.index')
                ->with('success', 'Matrícula reactivada correctamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible reactivar la matrícula.'));
        }
    }

    public function destroy(Request $request, Matricula$matricula): RedirectResponse
    {
        try {
            $this->authorize('delete', $matricula);$matricula->delete();

            return redirect()->route('academico.matriculas.index')
                ->with('success', 'Matrícula eliminada correctamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible eliminar la matrícula.'));
        }
    }

    protected function applyMatriculaScope($query, Usuario$usuario)
    {
        if ($usuario->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'secretaria', 'psicologa', 'Director', 'Subdirector', 'Gestor de Usuarios', 'Secretaria'])) {
            return $query;
        }

        $docente =$usuario->docente;
        if (!$docente) {
            return $query;
        }

        if ($usuario->hasAnyRole(['coordinador', 'Coordinador']) && $docente->es_coordinador &&$docente->modalidad_coordina_id) {
            return $query->whereHas('aula', fn ($q) => $q->where('modalidad_id',$docente->modalidad_coordina_id));
        }

        if ($usuario->hasAnyRole(['docente_guia', 'Docente Guia', 'Docente Guía'])) {
            return $query->where('aula_id', function ($subQ) use ($docente) {
                $subQ->select('id')->from('aula')->where('docente_guia_id',$docente->id);
            });
        }

        if ($usuario->hasAnyRole(['docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'Docente por Asignatura'])) {
            return $query->whereHas('aula.asignaciones', function ($q) use ($docente) {
                $q->where('docente_id',$docente->id)->where('activo', true);
            });
        }

        return $query;
    }

    protected function getAulasScope(Usuario $usuario)
    {
        $query = Aula::with(['grado', 'modalidad']);

        if ($usuario->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'secretaria', 'psicologa', 'Director', 'Subdirector', 'Gestor de Usuarios', 'Secretaria'])) {
            return $query->get();
        }

        $docente =$usuario->docente;
        if (!$docente) {
            return $query->get();
        }

        if ($usuario->hasAnyRole(['coordinador', 'Coordinador']) && $docente->es_coordinador &&$docente->modalidad_coordina_id) {
            return $query->where('modalidad_id',$docente->modalidad_coordina_id)->get();
        }

        if ($usuario->hasAnyRole(['docente_guia', 'Docente Guia', 'Docente Guía'])) {
            return $query->where('docente_guia_id',$docente->id)->get();
        }

        if ($usuario->hasAnyRole(['docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'Docente por Asignatura'])) {
            return $query->whereHas('asignaciones', function ($q) use ($docente) {
                $q->where('docente_id',$docente->id)->where('activo', true);
            })->get();
        }

        return $query->get();
    }
}