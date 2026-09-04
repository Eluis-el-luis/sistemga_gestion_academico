<?php

namespace App\Http\Controllers;

use App\Models\AulaAsignaturaDocente;
use App\Models\Matricula;
use App\Models\BloqueHorario;
use App\Models\AsistenciaAula;
use App\Models\AsistenciaAsignatura;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AsistenciaAsignaturaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Muestra la lista de alumnos para una asignatura específica y las incidencias del día.
     */
    public function create(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('gestionarAsistencia', $asignacion);

        $fecha = $request->query('fecha', Carbon::today()->toDateString());

        // 1. Alumnos matriculados en esta aula
        $matriculas = Matricula::with('alumno')
            ->where('aula_id', $asignacion->aula_id)
            ->where('estado', 'activo')
            ->get();

        // 2. Asistencia matutina del Guía (Para saber si el alumno reportó enfermedad temprano)
        $asistenciaGuia = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
            ->where('fecha', $fecha)
            ->get()
            ->keyBy('matricula_id');

        // 3. Incidencias previas reportadas por ESTE maestro en ESTA clase hoy
        $incidenciasPrevias = AsistenciaAsignatura::with('bloqueHorario')
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->where('asignatura_id', $asignacion->asignatura_id)
            ->where('fecha', $fecha)
            ->get()
            ->groupBy('matricula_id');

        

        // 4. Bloques horarios de esta modalidad/turno/jornada (excluyendo recreos) para que el maestro elija la hora
        $bloques = BloqueHorario::where('modalidad_id', $asignacion->aula->modalidad_id)
            ->where('turno', $asignacion->aula->turno)
            ->where('tipo_jornada', 'Regular')
            ->where('es_recreo', false)
            ->orderBy('hora_inicio')
            ->get();

        return view('academico.asistencia.asignatura.create', compact(
            'asignacion', 'matriculas', 'fecha', 'asistenciaGuia', 'incidenciasPrevias', 'bloques'
        ));
    }

    /**
     * Guarda una incidencia individual (Fuga, Retraso, Permiso).
     */
    public function store(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('gestionarAsistencia', $asignacion);

        $request->validate([
            'fecha' => 'required|date',
            'matricula_id' => 'required|exists:matricula,id',
            'bloque_horario_id' => 'required|exists:bloque_horario,id',
            // Restringimos estrictamente los estados que afectan al boletín
            'estado_incidencia' => 'required|in:Fuga,Llegada Tardía,Permiso de Salida',
            'observacion' => 'nullable|string|max:255',
        ]);

        // Validar que la matrícula pertenezca al aula de esta asignación
        $perteneceAula = Matricula::where('id', $request->matricula_id)
            ->where('aula_id', $asignacion->aula_id)
            ->where('estado', 'activo')
            ->exists();
        if (!$perteneceAula) {
            return back()->with('error', 'El estudiante no pertenece a esta aula o no está activo.');
        }

        // Validar que el bloque corresponda a modalidad, turno y jornada del aula
        $bloque = BloqueHorario::findOrFail($request->bloque_horario_id);
        if ($bloque->modalidad_id !== $asignacion->aula->modalidad_id
            || $bloque->turno !== $asignacion->aula->turno
            || $bloque->tipo_jornada !== 'Regular'
            || $bloque->es_recreo) {
            return back()->with('error', 'El bloque de tiempo seleccionado no corresponde a esta asignatura.');
        }

        // Guardamos o actualizamos (Evita que el maestro registre dos fugas para el mismo niño en el mismo bloque)
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
    }

    /**
     * Revierte (elimina) una incidencia si el maestro se equivocó.
     */
    public function destroy(AulaAsignaturaDocente $asignacion, AsistenciaAsignatura $incidencia)
    {
        $this->authorize('gestionarAsistencia', $asignacion);

        // Verificar que la incidencia pertenezca a ESTA asignación
        if ($incidencia->asignatura_id !== $asignacion->asignatura_id) {
            return back()->with('error', 'La incidencia no pertenece a esta asignatura.');
        }

        $incidencia->delete();
        return back()->with('success', 'Incidencia eliminada. El estudiante vuelve a contar como presente en este bloque.');
    }
}