<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\BloqueHorario;
use App\Models\Docente;
use App\Models\Horario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    use AuthorizesRequests;

    public function index(Aula $aula)
    {
        try {
            $this->authorize('horarios.ver');
            $aula->load(['grado', 'modalidad']);

            $asignaciones = AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                ->where('aula_id', $aula->id)
                ->get();

            $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->all();
            $todasAsignaturas = Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->orderBy('nombre')->get();
            $todosDocentes = Docente::with('usuario')->get();

            $bloquesOficiales = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                ->where('turno', $aula->turno)
                ->where('tipo_jornada', 'Regular')
                ->orderBy('hora_inicio')
                ->get();

            $bloquesAsignables = $bloquesOficiales->where('es_recreo', false);

            $horarios = Horario::with(['aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.docente.usuario', 'bloqueHorario'])
                ->whereIn('aula_asignatura_docente_id', $asignaciones->pluck('id'))
                ->get();

            $conteoHoras = $horarios->countBy('aula_asignatura_docente_id');
            foreach ($asignaciones as $asignacion) {
                $asignacion->horas_programadas = $conteoHoras->get($asignacion->id, 0);
                $asignacion->horas_restantes = (float) $asignacion->horas_semanales - (int) $asignacion->horas_programadas;
            }

            $aulaOcupada = [];
            foreach ($horarios as $h) {
                $aulaOcupada[$h->dia_semana][] = $h->bloque_horario_id;
            }

            $docentesIds = $asignaciones->pluck('docente_id')->filter()->unique();
            $horariosDocentes = Horario::with('aulaAsignaturaDocente')
                ->whereHas('aulaAsignaturaDocente', fn ($q) => $q->whereIn('docente_id', $docentesIds))
                ->get();

            $docentesOcupados = [];
            foreach ($horariosDocentes as $hd) {
                $docenteId = $hd->aulaAsignaturaDocente?->docente_id;
                if ($docenteId) {
                    $docentesOcupados[$docenteId][$hd->dia_semana][] = $hd->bloque_horario_id;
                }
            }

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloquesOficiales as $bloque) {
                $fila = ['bloque' => $bloque, 'dias' => []];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    $fila['dias'][$dia] = $bloque->es_recreo ? null : ($horariosIndex[$bloque->id][$diaBD] ?? null);
                }
                $matriz[] = $fila;
            }

            return view('academico.aulas.horarios.index', compact(
                'aula',
                'asignaciones',
                'bloquesOficiales',
                'bloquesAsignables',
                'matriz',
                'dias',
                'aulaOcupada',
                'docentesOcupados',
                'todasAsignaturas',
                'todosDocentes'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.gestor-horarios.index')->with('error', $this->formatearError($e, 'Ocurrió un error inesperado al intentar cargar el horario de esta aula.'));
        }
    }

    public function store(Request $request, Aula $aula)
    {
        $request->validate([
            'aula_asignatura_docente_id' => ['required', 'integer', 'exists:aula_asignatura_docente,id'],
            'dia_semana' => ['required', 'in:Lunes,Martes,Miercoles,Jueves,Viernes'],
            'bloque_horario_id' => ['required', 'integer', 'exists:bloque_horario,id'],
        ]);

        try {
            $this->authorize('horarios.gestionar');

            $asignacion = AulaAsignaturaDocente::with('aula.grado')->findOrFail($request->aula_asignatura_docente_id);
            if ((int) $asignacion->aula_id !== (int) $aula->id) {
                return back()->with('error', 'La materia seleccionada no pertenece a esta aula.');
            }

            $bloque = BloqueHorario::findOrFail($request->bloque_horario_id);
            if ((int) $bloque->modalidad_id !== (int) $aula->modalidad_id || $bloque->turno !== $aula->turno) {
                return back()->with('error', 'El bloque de tiempo no pertenece a la modalidad o turno de esta aula.');
            }
            if ($bloque->tipo_jornada !== 'Regular') {
                return back()->with('error', 'Este bloque pertenece a una jornada especial y no aplica para un horario regular.');
            }
            if ($bloque->es_recreo) {
                return back()->with('error', 'No se puede asignar una materia en un bloque de recreo.');
            }
            if (!$asignacion->docente_id) {
                return back()->with('error', 'No puedes asignar un horario a una materia que aún no tiene profesor titular.');
            }

            $choqueAula = Horario::whereHas('aulaAsignaturaDocente', fn ($q) => $q->where('aula_id', $aula->id))
                ->where('dia_semana', $request->dia_semana)
                ->where('bloque_horario_id', $request->bloque_horario_id)
                ->exists();

            if ($choqueAula) {
                return back()->with('error', 'Choque de Aula: Ya hay una materia asignada a esta sección en ese día y hora.');
            }

            $choqueDocente = Horario::with('aulaAsignaturaDocente.aula.grado')
                ->whereHas('aulaAsignaturaDocente', fn ($q) => $q->where('docente_id', $asignacion->docente_id))
                ->where('dia_semana', $request->dia_semana)
                ->where('bloque_horario_id', $request->bloque_horario_id)
                ->first();

            if ($choqueDocente) {
                $aulaOcupada = $choqueDocente->aulaAsignaturaDocente->aula->nombre ?? 'otra aula';
                $gradoOcupado = $choqueDocente->aulaAsignaturaDocente->aula->grado->nombre ?? '';

                return back()->with('error', "Choque de Maestro: El docente ya imparte clases en {$gradoOcupado} - {$aulaOcupada} durante ese bloque.");
            }

            Horario::create($request->only(['aula_asignatura_docente_id', 'dia_semana', 'bloque_horario_id']));

            return back()->with('success', 'Clase asignada al horario correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un error técnico al intentar guardar el horario.'));
        }
    }

    public function destroy(Aula $aula, Horario $horario)
    {
        try {
            $this->authorize('horarios.gestionar');
            $horario->delete();

            return back()->with('success', 'Bloque de horario eliminado.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el bloque del horario.'));
        }
    }
}