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
        try {
            $this->authorize('horarios.ver');

            $aula->load(['grado', 'modalidad']);

            $asignaciones = AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                                ->where('aula_id', $aula->id)
                                ->get();

            // Catálogos para gestionar docentes y materias extraordinarias desde esta misma vista
            $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->toArray();
            $todasAsignaturas = \App\Models\Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->orderBy('nombre')->get();
            $todosDocentes = \App\Models\Docente::with('usuario')->get();

            // 1. Bloques oficiales de ESTA aula (modalidad + turno + jornada Regular).
            $bloquesOficiales = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                                    ->where('turno', $aula->turno)
                                    ->where('tipo_jornada', 'Regular')
                                    ->orderBy('hora_inicio')
                                    ->get();

            // 1.5 Bloques para asignar (Filtramos los que son recreo/tiempo muerto)
            $bloquesAsignables = $bloquesOficiales->where('es_recreo', false);

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

            // --- INTELIGENCIA PARA LA INTERFAZ (Alpine.js) ---
            // A) ¿Qué bloques ya están ocupados en ESTA aula por día?
            $aulaOcupada = [];
            foreach ($horarios as $h) {
                $aulaOcupada[$h->dia_semana][] = $h->bloque_horario_id;
            }

            // B) ¿Qué bloques tienen ocupados LOS DOCENTES de esta aula (en todo el colegio)?
            $docentesIds = $asignaciones->pluck('docente_id')->filter()->unique();
            $horariosDocentes = Horario::whereHas('aulaAsignaturaDocente', function($query) use ($docentesIds) {
                $query->whereIn('docente_id', $docentesIds);
            })->get();
            
            $docentesOcupados = [];
            foreach ($horariosDocentes as $hd) {
                $docenteId = $hd->aulaAsignaturaDocente->docente_id;
                $docentesOcupados[$docenteId][$hd->dia_semana][] = $hd->bloque_horario_id;
            }

            // 4. Construir la matriz
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloquesOficiales as $bloque) {
                $fila = [
                    'bloque' => $bloque,
                    'dias' => [],
                ];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    if ($bloque->es_recreo) {
                        $fila['dias'][$dia] = null;
                    } else {
                        $horario = $horariosIndex[$bloque->id][$diaBD] ?? null;
                        $fila['dias'][$dia] = $horario;
                    }
                }
                $matriz[] = $fila;
            }

            return view('academico.aulas.horarios.index', compact(
                'aula', 'asignaciones', 'bloquesOficiales', 'bloquesAsignables', 'matriz', 'dias',
                'aulaOcupada', 'docentesOcupados', 'todasAsignaturas', 'todosDocentes'
            ));

        } catch (\Exception $e) {
            return redirect()->route('academico.gestor-horarios.index')->with('error', 'Ocurrió un error inesperado al intentar cargar el horario de esta aula.');
        }
    }

    public function store(Request $request, Aula $aula)
    {
        $request->validate([
            'aula_asignatura_docente_id' => 'required|exists:aula_asignatura_docente,id',
            'dia_semana' => 'required|in:Lunes,Martes,Miercoles,Jueves,Viernes',
            'bloque_horario_id' => 'required|exists:bloque_horario,id',
        ]);

        try {
            $this->authorize('horarios.gestionar');

            $asignacion = AulaAsignaturaDocente::with('aula.grado')->findOrFail($request->aula_asignatura_docente_id);

            if ($asignacion->aula_id !== $aula->id) {
                return back()->with('error', 'La materia seleccionada no pertenece a esta aula.');
            }

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

            if (!$asignacion->docente_id) {
                return back()->with('error', 'No puedes asignar un horario a una materia que aún no tiene profesor titular.');
            }

            $choqueAula = Horario::whereHas('aulaAsignaturaDocente', function($query) use ($aula) {
                $query->where('aula_id', $aula->id);
            })
            ->where('dia_semana', $request->dia_semana)
            ->where('bloque_horario_id', $request->bloque_horario_id)
            ->first();

            if ($choqueAula) {
                return back()->with('error', '¡Choque de Aula! Ya hay una materia asignada a esta sección en ese día y hora.');
            }

            $choqueDocente = Horario::with('aulaAsignaturaDocente.aula.grado')
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

            Horario::create($request->only(['aula_asignatura_docente_id', 'dia_semana', 'bloque_horario_id']));

            return back()->with('success', 'Clase asignada al horario correctamente.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Ocurrió un error técnico al intentar guardar el horario.');
        }
    }

    public function destroy(Aula $aula, Horario $horario)
    {
        try {
            $this->authorize('horarios.gestionar');
            $horario->delete();
            return back()->with('success', 'Bloque de horario eliminado.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo eliminar el bloque del horario.');
        }
    }
}