<?php

namespace App\Http\Controllers;

use App\Models\ActividadEvaluativa;
use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\Matricula;
use App\Models\NotaActividad;
use App\Models\Usuario;
use App\Services\NotaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActividadEvaluativaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, AulaAsignaturaDocente $asignacion)
    {
        try {
            $this->authorize('ver', $asignacion);

            /** @var Usuario $user */
            $user = Auth::user();
            $esDirectiva = $user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector']);
            $esCoordinador = $user->hasAnyRole(['coordinador', 'Coordinador']);
            $modoSupervision = $esDirectiva || $esCoordinador;

            $cortes = CorteEvaluativo::with('anioEscolar')
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->get();

            if ($cortes->isEmpty()) {
                return view('academico.notas.actividades', [
                    'asignacion' => $asignacion,
                    'modoSupervision' => $modoSupervision,
                    'cortes' => collect(),
                    'corteSeleccionado' => null,
                    'corteActivo' => null,
                    'actividades' => collect(),
                    'sumaAcumulados' => 0,
                    'sumaExamen' => 0,
                    'esCoordinador' => $esCoordinador,
                ]);
            }

            $corteSeleccionado = $request->query('corte_id', $cortes->first()->id);
            $corteActivo = $cortes->firstWhere('id', $corteSeleccionado);

            $actividades = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('corte_evaluativo_id', $corteSeleccionado)
                ->get();

            $sumaAcumulados = $actividades->where('tipo', 'acumulado')->sum('puntaje_maximo');
            $sumaExamen = $actividades->where('tipo', 'examen')->sum('puntaje_maximo');

            return view('academico.notas.actividades', compact(
                'asignacion',
                'modoSupervision',
                'cortes',
                'corteSeleccionado',
                'corteActivo',
                'actividades',
                'sumaAcumulados',
                'sumaExamen',
                'esCoordinador'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'Hubo un problema técnico al cargar las actividades evaluativas.'));
        }
    }

    public function store(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'puntaje_maximo' => ['required', 'numeric', 'min:1'],
            'corte_evaluativo_id' => ['required', 'integer', 'exists:corte_evaluativo,id'],
            'tipo' => ['required', 'in:acumulado,examen'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'fecha' => ['nullable', 'date'],
        ]);

        try {
            $this->authorize('calificar', $asignacion);

            $corte = CorteEvaluativo::findOrFail($request->corte_evaluativo_id);
            $sumaActual = (float) ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('corte_evaluativo_id', $corte->id)
                ->where('tipo', $request->tipo)
                ->sum('puntaje_maximo');

            $nuevoTotal = $sumaActual + (float) $request->puntaje_maximo;
            $limite = (float) ($request->tipo === 'acumulado' ? $corte->peso_acumulado : $corte->peso_examen);

            if ($limite > 0 && $nuevoTotal > $limite) {
                return back()->with('error', 'Límite excedido. Te restan ' . ($limite - $sumaActual) . ' puntos disponibles en el ' . $request->tipo . '.');
            }

            ActividadEvaluativa::create([
                'aula_asignatura_docente_id' => $asignacion->id,
                'corte_evaluativo_id' => $request->corte_evaluativo_id,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'puntaje_maximo' => $request->puntaje_maximo,
                'tipo' => $request->tipo,
                'fecha' => $request->fecha ?? now(),
            ]);

            return back()->with('success', 'Actividad guardada correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un error inesperado al intentar guardar la actividad.'));
        }
    }

    public function update(Request $request, AulaAsignaturaDocente $asignacion, ActividadEvaluativa $actividad)
    {
        $this->authorize('calificar', $asignacion);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'puntaje_maximo' => ['required', 'numeric', 'min:1'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'fecha' => ['nullable', 'date'],
        ]);

        try {
            $corte = CorteEvaluativo::findOrFail($actividad->corte_evaluativo_id);
            $sumaOtras = (float) ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('corte_evaluativo_id', $corte->id)
                ->where('tipo', $actividad->tipo)
                ->where('id', '!=', $actividad->id)
                ->sum('puntaje_maximo');

            $nuevoTotal = $sumaOtras + (float) $request->puntaje_maximo;
            $limite = (float) ($actividad->tipo === 'acumulado' ? $corte->peso_acumulado : $corte->peso_examen);

            if ($limite > 0 && $nuevoTotal > $limite) {
                return back()->with('error', 'Límite excedido. Te restan ' . ($limite - $sumaOtras) . ' puntos disponibles en el ' . $actividad->tipo . '.');
            }

            $actividad->update([
                'nombre' => $request->nombre,
                'puntaje_maximo' => $request->puntaje_maximo,
                'descripcion' => $request->descripcion,
                'fecha' => $request->fecha,
            ]);

            $this->recalcularNotasParcial($asignacion->id, $actividad->corte_evaluativo_id);

            return back()->with('success', 'Actividad actualizada y notas del corte recalculadas.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'Hubo un problema al actualizar esta actividad.'));
        }
    }

    public function destroy(AulaAsignaturaDocente $asignacion, ActividadEvaluativa $actividad)
    {
        try {
            $this->authorize('calificar', $asignacion);

            $corteId = $actividad->corte_evaluativo_id;
            $asignacionId = $asignacion->id;

            NotaActividad::where('actividad_evaluativa_id', $actividad->id)->delete();
            $actividad->delete();

            $this->recalcularNotasParcial($asignacionId, $corteId);

            return back()->with('success', 'Actividad eliminada y notas recalculadas.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'Hubo un error al intentar eliminar la actividad.'));
        }
    }

    protected function recalcularNotasParcial(int $asignacionId, int $corteId): void
    {
        $actividades = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacionId)
            ->where('corte_evaluativo_id', $corteId)
            ->get();

        $corte = CorteEvaluativo::find($corteId);
        $totalConfigurado = (float) $actividades->sum('puntaje_maximo');
        $totalPosible = (float) (($corte?->peso_acumulado ?? 0) + ($corte?->peso_examen ?? 0));
        if ($totalPosible <= 0) {
            $totalPosible = $totalConfigurado;
        }

        $actividadIds = $actividades->pluck('id');
        $asignacionModel = AulaAsignaturaDocente::find($asignacionId);
        if (!$asignacionModel) {
            return;
        }

        $matriculas = Matricula::where('aula_id', $asignacionModel->aula_id)
            ->where('estado', 'activo')
            ->pluck('id');

        foreach ($matriculas as $matriculaId) {
            $suma = (float) NotaActividad::where('matricula_id', $matriculaId)
                ->whereIn('actividad_evaluativa_id', $actividadIds)
                ->sum('nota_obtenida');

            app(NotaService::class)->registrarNotaFinal($matriculaId, $asignacionId, $corteId, $suma, $totalPosible, Auth::id());
        }
    }
}