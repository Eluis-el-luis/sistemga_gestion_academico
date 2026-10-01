<?php

namespace App\Http\Controllers;

use App\Models\GrupoMateria;
use App\Models\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GrupoMateriaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Listado de grupos con sus materias y formulario de asignación.
     */
    public function index()
    {
        // UX: Redirección suave en lugar del abort(403)
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para gestionar las agrupaciones de materias.');
        }

        try {
            $grupos = GrupoMateria::with('asignaturas')->orderBy('orden')->get();
            $asignaturas = Asignatura::orderBy('nombre')->get();

            return view('academico.grupo-materia.index', compact('grupos', 'asignaturas'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla la conexión a BD al cargar los grupos
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al cargar la gestión de agrupaciones.');
        }
    }

    /**
     * Crea un nuevo grupo de materias.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            return back()->with('error', 'No tiene permisos para crear agrupaciones.');
        }

        // Validación AFUERA del try-catch
        $request->validate([
            'nombre' => 'required|string|max:120',
            'orden' => 'nullable|integer|min:0',
        ]);

        try {
            GrupoMateria::create([
                'nombre' => $request->nombre,
                'orden' => $request->orden ?? 0,
            ]);

            return back()->with('success', 'Grupo de materias creado correctamente.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'Ocurrió un problema técnico al crear el grupo.');
        }
    }

    /**
     * Actualiza el nombre/orden de un grupo.
     */
    public function update(Request $request, GrupoMateria $grupo)
    {
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            return back()->with('error', 'No tiene permisos para editar agrupaciones.');
        }

        // Validación AFUERA del try-catch
        $request->validate([
            'nombre' => 'required|string|max:120',
            'orden' => 'nullable|integer|min:0',
        ]);

        try {
            $grupo->update([
                'nombre' => $request->nombre,
                'orden' => $request->orden ?? $grupo->orden,
            ]);

            return back()->with('success', 'Grupo actualizado correctamente.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'No se pudo actualizar el grupo en la base de datos.');
        }
    }

    /**
     * Elimina un grupo (las materias quedan sin grupo, es decir, en "Otros").
     */
    public function destroy(GrupoMateria $grupo)
    {
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            return back()->with('error', 'No tiene permisos para eliminar agrupaciones.');
        }

        try {
            // Desasignar las materias del grupo antes de eliminar
            Asignatura::where('grupo_materia_id', $grupo->id)->update(['grupo_materia_id' => null]);
            $grupo->delete();

            return back()->with('success', 'Grupo eliminado. Las materias asociadas quedaron sin grupo.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->with('error', 'No se pudo eliminar el grupo. Compruebe que no existan dependencias protegidas.');
        }
    }

    /**
     * Asigna materias a grupos (formulario masivo).
     */
    public function asignarMaterias(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Director', 'Subdirector'])) {
            return back()->with('error', 'No tiene permisos para asignar materias.');
        }

        // Validación AFUERA
        $request->validate([
            'asignaciones' => 'nullable|array',
            'asignaciones.*' => 'nullable|exists:grupo_materia,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->asignaciones ?? [] as $asignaturaId => $grupoId) {
                Asignatura::where('id', $asignaturaId)
                    ->update(['grupo_materia_id' => $grupoId ?: null]);
            }
            
            DB::commit();
            return back()->with('success', 'Asignación de materias a grupos actualizada correctamente.');
            
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            DB::rollBack();
            return back()->with('error', 'Hubo un problema al aplicar las asignaciones masivas. Ningún cambio fue guardado por seguridad.');
        }
    }
}