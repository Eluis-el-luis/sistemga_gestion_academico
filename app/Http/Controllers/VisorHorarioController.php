<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Docente;
use App\Models\Horario;
use Illuminate\Http\Request;

class VisorHorarioController extends Controller
{
    /**
     * Pantalla principal: Elegir entre Docentes o Aulas
     */
    public function index()
    {
        try {
            return view('academico.visor.index');
        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla al cargar la vista principal del visor
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al cargar el visor de horarios.');
        }
    }

    /**
     * Listado de todos los docentes
     */
    public function docentes()
    {
        try {
            // Traemos a los docentes con su usuario y ordenamos la colección con Laravel
            // Esto evita errores de nombres de tablas (users vs usuarios) en SQL
            $docentes = Docente::with('usuario')
                ->get()
                ->sortBy(function($docente) {
                    return $docente->usuario->nombre_completo ?? 'Z'; 
                });

            return view('academico.visor.docentes', compact('docentes'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla la consulta o el ordenamiento de docentes
            return redirect()->route('dashboard')->with('error', 'No se pudo cargar el listado de docentes para el visor.');
        }
    }

    /**
     * Horario específico de un Docente
     */
    public function horarioDocente(Docente $docente)
    {
        try {
            $docente->load('usuario');

            $horarios = Horario::with(['bloque', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.aula.grado'])
                ->whereHas('aulaAsignaturaDocente', function ($query) use ($docente) {
                    $query->where('docente_id', $docente->id);
                })
                ->get();

            // Bloques únicos que el docente tiene (ordenados por hora de inicio)
            $bloques = $horarios->map->bloque
                ->filter()
                ->unique('id')
                ->sortBy('hora_inicio');

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloques as $bloque) {
                $fila = ['bloque' => $bloque, 'dias' => []];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    $fila['dias'][$dia] = $horariosIndex[$bloque->id][$diaBD] ?? null;
                }
                $matriz[] = $fila;
            }

            return view('academico.visor.horario_docente', compact('docente', 'matriz', 'dias'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla la construcción de la matriz horaria del docente
            return redirect()->route('academico.visor.docentes')->with('error', 'Ocurrió un error al cargar el horario individual del docente.');
        }
    }

    /**
     * Listado de todas las aulas (Lockers para consulta)
     */
    public function aulas()
    {
        try {
            $aulas = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                ->orderBy('anio_escolar_id', 'desc')
                ->orderBy('grado_id')
                ->get();

            return view('academico.visor.aulas', compact('aulas'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla la consulta de aulas
            return redirect()->route('dashboard')->with('error', 'No se pudo cargar el listado de aulas para el visor.');
        }
    }

    /**
     * Horario específico de un Aula
     */
    public function horarioAula(Aula $aula)
    {
        try {
            $aula->load(['grado', 'modalidad', 'docenteGuia.usuario']);

            $asignaciones = \App\Models\AulaAsignaturaDocente::where('aula_id', $aula->id)->pluck('id');

            $horarios = Horario::with(['bloque', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.docente.usuario'])
                ->whereIn('aula_asignatura_docente_id', $asignaciones)
                ->get();

            // Bloques oficiales de la modalidad + turno + jornada Regular (incluye recreos)
            $bloques = \App\Models\BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                ->where('turno', $aula->turno)
                ->where('tipo_jornada', 'Regular')
                ->orderBy('hora_inicio')
                ->get();

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloques as $bloque) {
                $fila = ['bloque' => $bloque, 'dias' => []];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    if ($bloque->es_recreo) {
                        $fila['dias'][$dia] = null;
                    } else {
                        $fila['dias'][$dia] = $horariosIndex[$bloque->id][$diaBD] ?? null;
                    }
                }
                $matriz[] = $fila;
            }

            return view('academico.visor.horario_aula', compact('aula', 'matriz', 'dias'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla al armar la matriz del aula
            return redirect()->route('academico.visor.aulas')->with('error', 'Ocurrió un error al cargar el horario de esta aula.');
        }
    }
}