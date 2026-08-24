<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Horario;
use App\Models\AulaAsignaturaDocente;
use App\Models\BloqueHorario;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class HorarioController extends Controller
{
    use AuthorizesRequests;

    public function index(Aula $aula)
    {
        $this->authorize('horarios.ver');

        $aula->load(['grado', 'modalidad']);
        
        $asignaciones = AulaAsignaturaDocente::with('asignatura')
                            ->where('aula_id', $aula->id)
                            ->get();

        // 1. Buscamos los bloques oficiales para ESTA modalidad y ESTE turno
        $bloquesOficiales = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                                ->where('turno', $aula->turno)
                                ->orderBy('hora_inicio')
                                ->get();

        // 2. Traemos los horarios y los cargamos junto con su bloque oficial
        $horarios = Horario::with(['aulaAsignaturaDocente.asignatura', 'bloque'])
                    ->whereIn('aula_asignatura_docente_id', $asignaciones->pluck('id'))
                    ->get()
                    // Ordenamos usando la hora de inicio del bloque relacionado
                    ->sortBy(function($horario) {
                        return $horario->bloque->hora_inicio ?? '00:00:00';
                    });

        $calendario = [
            'Lunes' => $horarios->where('dia_semana', 'Lunes'),
            'Martes' => $horarios->where('dia_semana', 'Martes'),
            'Miércoles' => $horarios->where('dia_semana', 'Miércoles'),
            'Jueves' => $horarios->where('dia_semana', 'Jueves'),
            'Viernes' => $horarios->where('dia_semana', 'Viernes'),
        ];

        return view('academico.horarios.index', compact('aula', 'asignaciones', 'calendario', 'bloquesOficiales'));
    }

    public function store(Request $request, Aula $aula)
    {
        $this->authorize('horarios.gestionar');

        $request->validate([
            'aula_asignatura_docente_id' => 'required|exists:aula_asignatura_docente,id',
            'dia_semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes',
            'bloque_horario_id' => 'required|exists:bloque_horario,id',
        ]);

        // Validación extra: Evitar que se guarden dos materias en la misma aula, mismo día y misma hora
        $existe = Horario::whereHas('aulaAsignaturaDocente', function($query) use ($aula) {
            $query->where('aula_id', $aula->id);
        })
        ->where('dia_semana', $request->dia_semana)
        ->where('bloque_horario_id', $request->bloque_horario_id)
        ->first();

        if ($existe) {
            return back()->with('error', '¡Choque de horario! Ya hay una materia asignada en ese bloque y día.');
        }

        Horario::create($request->all());

        return back()->with('success', 'Bloque de horario agregado correctamente.');
    }

    public function destroy(Aula $aula, Horario $horario)
    {
        $this->authorize('horarios.gestionar');
        $horario->delete();
        return back()->with('success', 'Bloque de horario eliminado.');
    }
}