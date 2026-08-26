<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Matricula;
use App\Models\AsistenciaAula;
use App\Http\Requests\GuardarAsistenciaAulaRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AsistenciaAulaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Muestra la lista de alumnos del Docente Guía para pasar asistencia.
     */
    public function create(Request $request)
    {
        // Validamos que tenga el permiso base de Spatie (El Docente Guía lo tiene)
        $this->authorize('create', AsistenciaAula::class);

        $usuario = auth()->user();
        $docente = $usuario->docente;

        if (!$docente) {
            return redirect()->route('dashboard')->with('error', 'Su usuario no tiene un perfil de docente asociado.');
        }

        // Buscamos el aula activa donde este maestro es el guía
        $aula = Aula::where('docente_guia_id', $docente->id)
                    ->whereHas('anioEscolar', function($q) {
                        $q->where('activo', true);
                    })->first();

        if (!$aula) {
            return redirect()->route('dashboard')->with('error', 'No tiene un aula asignada como Docente Guía en el ciclo actual.');
        }

        // Capturamos la fecha solicitada (por defecto, hoy)
        $fecha = $request->query('fecha', Carbon::today()->toDateString());

        // Traemos a los alumnos matriculados y activos en esta aula
        $matriculas = Matricula::with('alumno')
            ->where('aula_id', $aula->id)
            ->where('estado', 'activo')
            ->get();

        // Traemos las asistencias de esa fecha (si ya las habían pasado temprano)
        // Usamos keyBy para que sea fácil buscarlas en la vista por el ID de la matrícula
        $asistenciasPrevias = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
            ->where('fecha', $fecha)
            ->get()
            ->keyBy('matricula_id');
            
        $incidenciasHoy = \App\Models\AsistenciaAsignatura::with(['asignatura'])
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->where('fecha', $fecha)
            ->get();

        return view('academico.asistencia.aula.create', compact('aula', 'matriculas', 'fecha', 'asistenciasPrevias', 'incidenciasHoy'));
    }

    /**
     * Guarda o actualiza la asistencia masiva enviada desde el formulario.
     */
    public function store(GuardarAsistenciaAulaRequest $request)
    {
        $this->authorize('create', AsistenciaAula::class);

        $datos = $request->validated();
        $fecha = $datos['fecha'];

        foreach ($datos['asistencias'] as $asistencia) {
            AsistenciaAula::updateOrCreate(
                [
                    'matricula_id' => $asistencia['matricula_id'], 
                    'fecha' => $fecha
                ],
                [
                    'estado_asistencia' => $asistencia['estado_asistencia']
                ]
            );
        }

        return back()->with('success', 'Asistencia del aula registrada/actualizada exitosamente para la fecha: ' . Carbon::parse($fecha)->format('d/m/Y'));
    }
}