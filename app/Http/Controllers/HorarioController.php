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
        
        $asignaciones = AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                            ->where('aula_id', $aula->id)
                            ->get();

        // 1. Buscamos los bloques oficiales
        $bloquesOficiales = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                                ->where('turno', $aula->turno)
                                ->orderBy('hora_inicio')
                                ->get();

        // 2. Traemos los horarios
        $horarios = Horario::with(['aulaAsignaturaDocente.asignatura', 'bloque'])
                    ->whereIn('aula_asignatura_docente_id', $asignaciones->pluck('id'))
                    ->get();

        // 3. --- NUEVA MAGIA: Cálculo de la Bolsa de Horas ---
        // Contamos cuántas veces aparece cada asignación en el horario actual
        $conteoHoras = $horarios->countBy('aula_asignatura_docente_id');

        // A cada asignación le inyectamos las horas que ha consumido y las restantes
        foreach ($asignaciones as $asignacion) {
            $asignacion->horas_programadas = $conteoHoras->get($asignacion->id, 0);
            $asignacion->horas_restantes = $asignacion->horas_semanales - $asignacion->horas_programadas;
        }
        // ----------------------------------------------------

        // Ordenamos los horarios por hora de inicio
        $horarios = $horarios->sortBy(function($horario) {
            return $horario->bloque->hora_inicio ?? '00:00:00';
        });

        $calendario = [
            'Lunes' => $horarios->where('dia_semana', 'Lunes'),
            'Martes' => $horarios->where('dia_semana', 'Martes'),
            'Miércoles' => $horarios->where('dia_semana', 'Miércoles'),
            'Jueves' => $horarios->where('dia_semana', 'Jueves'),
            'Viernes' => $horarios->where('dia_semana', 'Viernes'),
        ];

        return view('academico.aulas.horarios.index', compact('aula', 'asignaciones', 'calendario', 'bloquesOficiales'));
    }

    public function store(Request $request, Aula $aula)
    {
        $this->authorize('horarios.gestionar');

        $request->validate([
            'aula_asignatura_docente_id' => 'required|exists:aula_asignatura_docente,id',
            'dia_semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes',
            'bloque_horario_id' => 'required|exists:bloque_horario,id',
        ]);

        // 1. Obtener la asignación solicitada para saber quién es el maestro
        $asignacion = AulaAsignaturaDocente::with('aula.grado')->findOrFail($request->aula_asignatura_docente_id);

        // 2. Escudo de Integridad: ¿Tiene profesor asignado?
        if (!$asignacion->docente_id) {
            return back()->with('error', 'No puedes asignar un horario a una materia que aún no tiene profesor titular.');
        }

        // 3. Escudo Choque de Aula: ¿El aula ya tiene clase a esta hora?
        $choqueAula = Horario::whereHas('aulaAsignaturaDocente', function($query) use ($aula) {
            $query->where('aula_id', $aula->id);
        })
        ->where('dia_semana', $request->dia_semana)
        ->where('bloque_horario_id', $request->bloque_horario_id)
        ->first();

        if ($choqueAula) {
            return back()->with('error', '¡Choque de Aula! Ya hay una materia asignada a esta sección en ese día y hora.');
        }

        // 4. Escudo Choque de Docente: ¿El profesor está en otra aula a esta hora?
        $choqueDocente = Horario::with('aulaAsignaturaDocente.aula.grado') // Cargamos la relación para el mensaje de error
        ->whereHas('aulaAsignaturaDocente', function($query) use ($asignacion) {
            $query->where('docente_id', $asignacion->docente_id);
        })
        ->where('dia_semana', $request->dia_semana)
        ->where('bloque_horario_id', $request->bloque_horario_id)
        ->first();

        if ($choqueDocente) {
            $aulaOcupada = $choqueDocente->aulaAsignaturaDocente->aula->nombre ?? 'otra aula';
            $gradoOcupado = $choqueDocente->aulaAsignaturaDocente->aula->grado->nombre ?? '';
            
            return back()->with('error', "¡Choque de Maestro! El docente ya imparte clases en {$gradoOcupado} - {$aulaOcupada} durante ese bloque.");
        }

        // 5. Vía Libre: Guardar
        Horario::create($request->all());

        return back()->with('success', 'Clase asignada al horario correctamente.');
    }

    public function destroy(Aula $aula, Horario $horario)
    {
        $this->authorize('horarios.gestionar');
        $horario->delete();
        return back()->with('success', 'Bloque de horario eliminado.');
    }
}