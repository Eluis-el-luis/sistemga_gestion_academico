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

        // Buscamos TODOS los horarios donde este docente imparte clase
        $horarios = Horario::with(['bloque', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.aula.grado'])
            ->whereHas('aulaAsignaturaDocente', function ($query) use ($docente) {
                $query->where('docente_id', $docente->id);
            })
            ->get()
            // Ordenamos estrictamente por la hora de inicio (ya que puede mezclar primaria y secundaria)
            ->sortBy(function ($horario) {
                return $horario->bloque->hora_inicio ?? '00:00:00';
            });

        // Agrupamos por días
        $calendario = [
            'Lunes'     => $horarios->where('dia_semana', 'Lunes'),
            'Martes'    => $horarios->where('dia_semana', 'Martes'),
            'Miércoles' => $horarios->where('dia_semana', 'Miércoles'),
            'Jueves'    => $horarios->where('dia_semana', 'Jueves'),
            'Viernes'   => $horarios->where('dia_semana', 'Viernes'),
        ];

        return view('academico.visor.horario_docente', compact('docente', 'calendario'));
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

        $horarios = Horario::with(['bloque', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.docente.usuario'])
            ->whereHas('aulaAsignaturaDocente', function ($query) use ($aula) {
                $query->where('aula_id', $aula->id);
            })
            ->get()
            ->sortBy(function ($horario) {
                return $horario->bloque->hora_inicio ?? '00:00:00';
            });

        $calendario = [
            'Lunes'     => $horarios->where('dia_semana', 'Lunes'),
            'Martes'    => $horarios->where('dia_semana', 'Martes'),
            'Miércoles' => $horarios->where('dia_semana', 'Miércoles'),
            'Jueves'    => $horarios->where('dia_semana', 'Jueves'),
            'Viernes'   => $horarios->where('dia_semana', 'Viernes'),
        ];

        return view('academico.visor.horario_aula', compact('aula', 'calendario'));
    }
}