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

        // 1. Bloques oficiales de ESTA aula (modalidad + turno + jornada Regular).
        $bloquesOficiales = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                                ->where('turno', $aula->turno)
                                ->where('tipo_jornada', 'Regular')
                                ->orderBy('hora_inicio')
                                ->get();

        // 2. Horarios (materias) ya programados para este aula.
        $horarios = Horario::with(['aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.docente.usuario', 'bloque'])
                    ->whereIn('aula_asignatura_docente_id', $asignaciones->pluck('id'))
                    ->get();

        // 3. Bolsa de horas por asignación.
        $conteoHoras = $horarios->countBy('aula_asignatura_docente_id');
        foreach ($asignaciones as $asignacion) {
            $asignacion->horas_programadas = $conteoHoras->get($asignacion->id, 0);
            $asignacion->horas_restantes = $asignacion->horas_semanales - $asignacion->horas_programadas;
        }

        // 4. Construir la matriz: filas = bloques oficiales (incluye recreos),
        //    columnas = días. Cada celda es la materia asignada o null.
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

        // Índice rápido: [bloque_id][dia_bd] => Horario
        $horariosIndex = [];
        foreach ($horarios as $h) {
            $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
        }

        // Estructura para la vista: lista de bloques con sus celdas por día
        $matriz = [];
        foreach ($bloquesOficiales as $bloque) {
            $fila = [
                'bloque' => $bloque,
                'dias' => [],
            ];
            foreach ($dias as $i => $dia) {
                $diaBD = $diasBD[$i];
                if ($bloque->es_recreo) {
                    // El recreo es fijo, sin materia
                    $fila['dias'][$dia] = null;
                } else {
                    $horario = $horariosIndex[$bloque->id][$diaBD] ?? null;
                    $fila['dias'][$dia] = $horario;
                }
            }
            $matriz[] = $fila;
        }

        return view('academico.aulas.horarios.index', compact('aula', 'asignaciones', 'bloquesOficiales', 'matriz', 'dias'));
    }

    public function store(Request $request, Aula $aula)
    {
        $this->authorize('horarios.gestionar');

        $request->validate([
            'aula_asignatura_docente_id' => 'required|exists:aula_asignatura_docente,id',
            'dia_semana' => 'required|in:Lunes,Martes,Miercoles,Jueves,Viernes',
            'bloque_horario_id' => 'required|exists:bloque_horario,id',
        ]);

        // 1. Obtener la asignación solicitada para saber quién es el maestro
        $asignacion = AulaAsignaturaDocente::with('aula.grado')->findOrFail($request->aula_asignatura_docente_id);

        // 1.5 Escudo de Pertinencia: la asignación debe pertenecer a ESTA aula
        if ($asignacion->aula_id !== $aula->id) {
            return back()->with('error', 'La materia seleccionada no pertenece a esta aula.');
        }

        // 1.6 Escudo de Bloque: el bloque debe pertenecer a la modalidad, turno y jornada del aula
        $bloque = BloqueHorario::findOrFail($request->bloque_horario_id);
        if ($bloque->modalidad_id !== $aula->modalidad_id || $bloque->turno !== $aula->turno) {
            return back()->with('error', 'El bloque de tiempo no pertenece a la modalidad o turno de esta aula.');
        }
        if ($bloque->tipo_jornada !== 'Regular') {
            return back()->with('error', 'Este bloque pertenece a una jornada especial y no aplica para un horario regular.');
        }
        if ($bloque->es_recreo) {
            return back()->with('error', 'No se puede asignar una materia en un bloque de recreo.');
        }

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
        Horario::create($request->only(['aula_asignatura_docente_id', 'dia_semana', 'bloque_horario_id']));

        return back()->with('success', 'Clase asignada al horario correctamente.');
    }

    public function destroy(Aula $aula, Horario $horario)
    {
        $this->authorize('horarios.gestionar');
        $horario->delete();
        return back()->with('success', 'Bloque de horario eliminado.');
    }
}