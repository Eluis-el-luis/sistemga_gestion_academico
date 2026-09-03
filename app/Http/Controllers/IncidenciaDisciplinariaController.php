<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IncidenciaDisciplinaria;
use Illuminate\Support\Facades\Auth;

class IncidenciaDisciplinariaController extends Controller
{
    // 1. Visor del buzón para el Coordinador
    public function index()
    {
        // Traemos las incidencias ordenadas por prioridad (Las 'Reportadas' de primero)
        $incidencias = IncidenciaDisciplinaria::with(['matricula.alumno', 'matricula.aula.grado', 'docenteReporta.usuario', 'coordinadorAtiende'])
            ->orderByRaw("
                CASE 
                    WHEN estado = 'Reportada' THEN 1 
                    WHEN estado = 'Citación a Padres' THEN 2 
                    WHEN estado = 'En Revisión' THEN 3 
                    ELSE 4 
                END
            ")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('academico.disciplina.index', compact('incidencias'));
    }

    // 2. El Docente o Maestro Guía registra una nueva falta
    public function store(Request $request)
    {
        $request->validate([
            'matricula_id' => 'required|exists:matricula,id',
            'nivel_falta' => 'required|in:Leve,Grave,Muy Grave',
            'descripcion' => 'required|string|max:1000',
            'fecha_incidencia' => 'required|date',
        ]);

        IncidenciaDisciplinaria::create([
            'matricula_id' => $request->matricula_id,
            'docente_reporta_id' => Auth::user()->docente->id,
            'nivel_falta' => $request->nivel_falta,
            'descripcion' => $request->descripcion,
            'fecha_incidencia' => $request->fecha_incidencia,
            'estado' => 'Reportada'
        ]);

        return back()->with('success', 'Incidencia reportada exitosamente al Coordinador.');
    }

    // 3. El Coordinador atiende el caso (Cambia estado, cita padres o cierra el caso)
    public function update(Request $request, IncidenciaDisciplinaria $incidencia)
    {
        $request->validate([
            'estado' => 'required|in:Reportada,En Revisión,Citación a Padres,Cerrada',
            'fecha_citacion_padres' => 'nullable|date',
            'resolucion_final' => 'nullable|string|max:1000'
        ]);

        $incidencia->update([
            'estado' => $request->estado,
            'coordinador_atiende_id' => Auth::id(), // Registra quién atendió el caso
            'fecha_citacion_padres' => $request->fecha_citacion_padres,
            'resolucion_final' => $request->resolucion_final
        ]);

        return back()->with('success', 'Estado de la incidencia actualizado correctamente.');
    }
}
