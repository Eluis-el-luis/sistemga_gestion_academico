<?php

namespace App\Http\Controllers;

use App\Models\GrupoMateria;
use App\Models\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GrupoMateriaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Listado de grupos con sus materias y formulario de asignación.
     */
    public function index()
    {
        // Solo Dirección y Subdirección gestionan las agrupaciones de materias
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'No tiene permisos para gestionar las agrupaciones de materias.');
        }

        $grupos = GrupoMateria::with('asignaturas')->orderBy('orden')->get();
        $asignaturas = Asignatura::orderBy('nombre')->get();

        return view('academico.grupo-materia.index', compact('grupos', 'asignaturas'));
    }

    /**
     * Crea un nuevo grupo de materias.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:120',
            'orden' => 'nullable|integer|min:0',
        ]);

        GrupoMateria::create([
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? 0,
        ]);

        return back()->with('success', 'Grupo de materias creado correctamente.');
    }

    /**
     * Actualiza el nombre/orden de un grupo.
     */
    public function update(Request $request, GrupoMateria $grupo)
    {
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:120',
            'orden' => 'nullable|integer|min:0',
        ]);

        $grupo->update([
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? $grupo->orden,
        ]);

        return back()->with('success', 'Grupo actualizado correctamente.');
    }

    /**
     * Elimina un grupo (las materias quedan sin grupo, es decir, en "Otros").
     */
    public function destroy(GrupoMateria $grupo)
    {
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403);
        }

        // Desasignar las materias del grupo antes de eliminar
        Asignatura::where('grupo_materia_id', $grupo->id)->update(['grupo_materia_id' => null]);
        $grupo->delete();

        return back()->with('success', 'Grupo eliminado. Las materias asociadas quedaron sin grupo.');
    }

    /**
     * Asigna materias a grupos (formulario masivo).
     */
    public function asignarMaterias(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403);
        }

        $request->validate([
            'asignaciones' => 'nullable|array',
            'asignaciones.*' => 'nullable|exists:grupo_materia,id',
        ]);

        foreach ($request->asignaciones ?? [] as $asignaturaId => $grupoId) {
            Asignatura::where('id', $asignaturaId)
                ->update(['grupo_materia_id' => $grupoId ?: null]);
        }

        return back()->with('success', 'Asignación de materias a grupos actualizada correctamente.');
    }
}