<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\GrupoMateria;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrupoMateriaController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para gestionar las agrupaciones de materias.');
        }

        try {
            $grupos = GrupoMateria::with('asignaturas')->orderBy('orden')->get();
            $asignaturas = Asignatura::orderBy('nombre')->get();

            return view('academico.grupo-materia.index', compact('grupos', 'asignaturas'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error al cargar la gestión de agrupaciones.'));
        }
    }

    public function store(Request $request)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            return back()->with('error', 'No tiene permisos para crear agrupaciones.');
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            GrupoMateria::create([
                'nombre' => $request->nombre,
                'orden' => $request->orden ?? 0,
            ]);

            return back()->with('success', 'Grupo de materias creado correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un problema técnico al crear el grupo.'));
        }
    }

    public function update(Request $request, GrupoMateria $grupo)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            return back()->with('error', 'No tiene permisos para editar agrupaciones.');
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $grupo->update([
                'nombre' => $request->nombre,
                'orden' => $request->orden ?? $grupo->orden,
            ]);

            return back()->with('success', 'Grupo actualizado correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'No se pudo actualizar el grupo en la base de datos.'));
        }
    }

    public function destroy(GrupoMateria $grupo)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            return back()->with('error', 'No tiene permisos para eliminar agrupaciones.');
        }

        DB::beginTransaction();
        try {
            Asignatura::where('grupo_materia_id', $grupo->id)->update(['grupo_materia_id' => null]);
            $grupo->delete();
            DB::commit();

            return back()->with('success', 'Grupo eliminado. Las materias asociadas quedaron sin grupo.');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el grupo.'));
        }
    }

    public function asignarMaterias(Request $request)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
            return back()->with('error', 'No tiene permisos para asignar materias.');
        }

        $request->validate([
            'asignaciones' => ['nullable', 'array'],
            'asignaciones.*' => ['nullable', 'integer', 'exists:grupo_materia,id'],
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->asignaciones ?? [] as $asignaturaId => $grupoId) {
                Asignatura::where('id', (int) $asignaturaId)
                    ->update(['grupo_materia_id' => $grupoId ?: null]);
            }
            DB::commit();

            return back()->with('success', 'Asignación de materias a grupos actualizada correctamente.');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $this->formatearError($e, 'Hubo un problema al aplicar las asignaciones masivas.'));
        }
    }
}