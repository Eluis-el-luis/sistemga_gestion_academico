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
        try {
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
            
        } catch (\Exception $e) {
            // CONTINGENCIA: Si la consulta personalizada con orderByRaw falla en la BD
            return redirect()->route('dashboard')->with('error', 'Ocurrió un problema al cargar el buzón de incidencias disciplinarias.');
        }
    }

    // 2. El Docente o Maestro Guía registra una nueva falta
    public function store(Request $request)
    {
        // Validación AFUERA del try-catch para los errores en el formulario
        $request->validate([
            'matricula_id' => 'required|exists:matricula,id',
            'nivel_falta' => 'required|in:Leve,Grave,Muy Grave',
            'descripcion' => 'required|string|max:1000',
            'fecha_incidencia' => 'required|date',
        ]);

        try {
            $docente = Auth::user()->docente;
            
            // Protección contra usuarios sin perfil docente asociado
            if (!$docente) {
                return back()->with('error', 'Su usuario no tiene un perfil de docente asociado para reportar incidencias.');
            }

            IncidenciaDisciplinaria::create([
                'matricula_id' => $request->matricula_id,
                'docente_reporta_id' => $docente->id,
                'nivel_falta' => $request->nivel_falta,
                'descripcion' => $request->descripcion,
                'fecha_incidencia' => $request->fecha_incidencia,
                'estado' => 'Reportada'
            ]);

            return back()->with('success', 'Incidencia reportada exitosamente al Coordinador.');
            
        } catch (\Exception $e) {
            // CONTINGENCIA: Si la base de datos rechaza la creación
            return back()->withInput()->with('error', 'Hubo un error técnico al reportar la incidencia. Verifica los datos e intenta nuevamente.');
        }
    }

    // 3. El Coordinador atiende el caso (Cambia estado, cita padres o cierra el caso)
    public function update(Request $request, IncidenciaDisciplinaria $incidencia)
    {
        // Validación AFUERA
        $request->validate([
            'estado' => 'required|in:Reportada,En Revisión,Citación a Padres,Cerrada',
            'fecha_citacion_padres' => 'nullable|date',
            'resolucion_final' => 'nullable|string|max:1000'
        ]);

        try {
            $incidencia->update([
                'estado' => $request->estado,
                'coordinador_atiende_id' => Auth::id(), // Registra quién atendió el caso
                'fecha_citacion_padres' => $request->fecha_citacion_padres,
                'resolucion_final' => $request->resolucion_final
            ]);

            return back()->with('success', 'Estado de la incidencia actualizado correctamente.');
            
        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla el UPDATE
            return back()->withInput()->with('error', 'No se pudo actualizar el estado de la incidencia debido a un fallo de conexión.');
        }
    }
}