<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\ExamenReparacion;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Usuario;
use App\Services\ReparacionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamenReparacionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReparacionService $reparacionService
    ) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', ExamenReparacion::class);

            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $anioActivo = AnioEscolar::where('estado', 'activo')->first();
            $gradosAutoPromovidos = [1, 2, 3, 4, 5];

            $matriculasRaw = Matricula::with(['alumno', 'aula.grado'])
                ->when($anioActivo, fn ($q) => $q->where('anio_escolar_id', $anioActivo->id))
                ->where('estado', 'activo')
                ->whereHas('aula', fn ($q) => $q->whereNotIn('grado_id', $gradosAutoPromovidos))
                ->when($usuario->docente && !$usuario->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector']), function ($q) use ($usuario) {
                    $q->whereHas('aula', fn ($q2) => $q2->where('docente_guia_id', $usuario->docente->id));
                })
                ->get();

            $matriculas = $matriculasRaw->map(function ($matricula) {
                $matricula->clases_reprobadas = Nota::with('aulaAsignaturaDocente.asignatura')
                    ->where('matricula_id', $matricula->id)
                    ->whereNotNull('nota_cuantitativa')
                    ->where('nota_cuantitativa', '<', 60)
                    ->get();

                return $matricula;
            })
                ->filter(fn ($matricula) => $matricula->clases_reprobadas->count() > 0)
                ->values();

            return view('academico.reparacion.index', compact('matriculas'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al cargar el listado de alumnos en reparación.'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula_id' => ['required', 'integer', 'exists:matricula,id'],
            'asignatura_id' => ['required', 'integer', 'exists:asignatura,id'],
            'nota_obtenida' => ['required', 'numeric', 'min:0', 'max:100'],
            'fecha' => ['required', 'date'],
        ]);

        try {
            $this->authorize('create', ExamenReparacion::class);

            $matricula = Matricula::findOrFail($request->matricula_id);
            $asignatura = Asignatura::findOrFail($request->asignatura_id);

            $this->reparacionService->registrar($matricula, $asignatura, (float) $request->nota_obtenida, $request->fecha);

            return back()->with('success', 'Examen de reparación registrado correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un error técnico al registrar la nota de reparación.'));
        }
    }

    public function destroy(ExamenReparacion $examen)
    {
        try {
            $this->authorize('delete', $examen);
            $examen->delete();

            return back()->with('success', 'Examen de reparación eliminado.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el examen de reparación.'));
        }
    }
}