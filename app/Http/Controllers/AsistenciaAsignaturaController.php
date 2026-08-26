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
        // Seguridad: Solo el dueño de esta clase (o un superior) puede entrar
        if ($asignacion->docente->usuario_id !== auth()->id() && !auth()->user()->hasRole(['Director', 'Subdirector'])) {
            abort(403, 'No tiene permisos para registrar asistencia en esta clase.');
        }

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

        

        // 4. Bloques horarios de esta modalidad (excluyendo recreos) para que el maestro elija la hora
        $bloques = BloqueHorario::where('modalidad_id', $asignacion->aula->modalidad_id)
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
        if ($asignacion->docente->usuario_id !== auth()->id() && !auth()->user()->hasRole(['Director', 'Subdirector'])) {
            abort(403);
        }

        $request->validate([
            'fecha' => 'required|date',
            'matricula_id' => 'required|exists:matricula,id',
            'bloque_horario_id' => 'required|exists:bloque_horario,id',
            // Restringimos estrictamente los estados que afectan al boletín
            'estado_incidencia' => 'required|in:Fuga,Llegada Tardía,Permiso de Salida',
            'observacion' => 'nullable|string|max:255',
        ]);

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
    public function destroy(AsistenciaAsignatura $incidencia)
    {
        $incidencia->delete();
        return back()->with('success', 'Incidencia eliminada. El estudiante vuelve a contar como presente en este bloque.');
    }
}