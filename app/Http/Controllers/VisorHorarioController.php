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
        return view('academico.visor.index');
    }

    /**
     * Listado de todos los docentes
     */
    /**
     * Listado de todos los docentes
     */
    public function docentes()
    {
        // Traemos a los docentes con su usuario y ordenamos la colección con Laravel
        // Esto evita errores de nombres de tablas (users vs usuarios) en SQL
        $docentes = Docente::with('usuario')
            ->get()
            ->sortBy(function($docente) {
                return $docente->usuario->nombre_completo ?? 'Z'; 
            });

        return view('academico.visor.docentes', compact('docentes'));
    }

    /**
     * Horario específico de un Docente
     */
public function horarioDocente(Docente $docente)
    {
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
    }

    /**
     * Listado de todas las aulas (Lockers para consulta)
     */
    public function aulas()
    {
        $aulas = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
            ->orderBy('anio_escolar_id', 'desc')
            ->orderBy('grado_id')
            ->get();

        return view('academico.visor.aulas', compact('aulas'));
    }

    /**
     * Horario específico de un Aula
     */
public function horarioAula(Aula $aula)
    {
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
    }
}