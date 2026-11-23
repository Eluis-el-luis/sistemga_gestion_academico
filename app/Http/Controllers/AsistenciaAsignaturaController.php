<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaAsignatura;
use App\Models\AsistenciaAula;
use App\Models\AulaAsignaturaDocente;
use App\Models\BloqueHorario;
use App\Models\Matricula;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AsistenciaAsignaturaController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request, AulaAsignaturaDocente $asignacion)
    {
        try {
            $this->authorize('gestionarAsistencia', $asignacion);
            $asignacion->load('aula', 'asignatura');

            if (!$asignacion->aula) {
                return redirect()->route('dashboard')->with('error', 'Esta asignación no tiene aula asociada.');
            }

            $fecha = $request->query('fecha', Carbon::today()->toDateString());
            $matriculas = Matricula::with('alumno')
                ->where('aula_id', $asignacion->aula_id)
                ->where('estado', 'activo')
                ->get();

            $asistenciaGuia = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
                ->where('fecha', $fecha)
                ->get()
                ->keyBy('matricula_id');

            $incidenciasPrevias = AsistenciaAsignatura::with('bloqueHorario')
                ->whereIn('matricula_id', $matriculas->pluck('id'))
                ->where('asignatura_id', $asignacion->asignatura_id)
                ->where('fecha', $fecha)
                ->get()
                ->groupBy('matricula_id');

            $bloques = BloqueHorario::where('modalidad_id', $asignacion->aula->modalidad_id)
                ->where('turno', $asignacion->aula->turno)
                ->where('tipo_jornada', 'Regular')
                ->where('es_recreo', false)
                ->orderBy('hora_inicio')
                ->get();

            return view('academico.asistencia.asignatura.create', compact(
                'asignacion',
                'matriculas',
                'fecha',
                'asistenciaGuia',
                'incidenciasPrevias',
                'bloques'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error al cargar la planilla de asistencia.'));
        }
    }

    public function store(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $request->validate([
            'fecha' => ['required', 'date'],
            'matricula_id' => ['required', 'integer', 'exists:matricula,id'],
            'bloque_horario_id' => ['required', 'integer', 'exists:bloque_horario,id'],
            'estado_incidencia' => ['required', 'in:Fuga,Llegada Tardía,Permiso de Salida'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->authorize('gestionarAsistencia', $asignacion);
            $asignacion->load('aula');

            $perteneceAula = Matricula::where('id', $request->matricula_id)
                ->where('aula_id', $asignacion->aula_id)
                ->where('estado', 'activo')
                ->exists();

            if (!$perteneceAula) {
                return back()->with('error', 'El estudiante no pertenece a esta aula o no está activo.');
            }

            $bloque = BloqueHorario::findOrFail($request->bloque_horario_id);
            if (!$asignacion->aula
                || (int) $bloque->modalidad_id !== (int) $asignacion->aula->modalidad_id
                || $bloque->turno !== $asignacion->aula->turno
                || $bloque->tipo_jornada !== 'Regular'
                || $bloque->es_recreo) {
                return back()->with('error', 'El bloque de tiempo seleccionado no corresponde a esta asignatura.');
            }

            AsistenciaAsignatura::updateOrCreate(
                [
                    'matricula_id' => $request->matricula_id,
                    'asignatura_id' => $asignacion->asignatura_id,
                    'bloque_horario_id' => $request->bloque_horario_id,
                    'fecha' => $request->fecha,
                ],
                [
                    'estado_incidencia' => $request->estado_incidencia,
                    'observacion' => $request->observacion,
                ]
            );

            return back()->with('success', 'Incidencia registrada exitosamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un problema al guardar la incidencia.'));
        }
    }

    public function destroy(AulaAsignaturaDocente $asignacion, AsistenciaAsignatura $incidencia)
    {
        try {
            $this->authorize('gestionarAsistencia', $asignacion);

            if ((int) $incidencia->asignatura_id !== (int) $asignacion->asignatura_id) {
                return back()->with('error', 'La incidencia no pertenece a esta asignatura.');
            }

            $incidencia->delete();

            return back()->with('success', 'Incidencia eliminada. El estudiante vuelve a contar como presente en este bloque.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar la incidencia.'));
        }
    }
}