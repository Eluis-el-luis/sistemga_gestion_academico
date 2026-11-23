<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsignaturaController extends Controller
{
    public function index()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'coordinador', 'gestor_it', 'Director', 'Subdirector', 'Coordinador', 'Gestor de Usuarios'])) {
            abort(403, 'No tienes permiso para acceder al catálogo del currículo.');
        }

        try {
            $asignaturas = Asignatura::orderBy('nombre', 'asc')->get();

            return view('academico.asignaturas.index', compact('asignaturas'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error inesperado al cargar el catálogo de asignaturas.'));
        }
    }

    public function store(Request $request)
    {
        $request->merge(['nombre' => trim((string) $request->nombre)]);

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    $existe = Asignatura::whereRaw('LOWER(nombre) = ?', [mb_strtolower((string) $value, 'UTF-8')])->exists();
                    if ($existe) {
                        $fail('Esta asignatura ya está registrada en el catálogo.');
                    }
                },
            ],
        ]);

        try {
            Asignatura::create(['nombre' => $request->nombre]);

            return back()->with('success', 'Asignatura registrada correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un problema técnico al registrar la asignatura.'));
        }
    }

    public function update(Request $request, Asignatura $asignatura)
    {
        $request->merge(['nombre' => trim((string) $request->nombre)]);

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($asignatura) {
                    $existe = Asignatura::whereRaw('LOWER(nombre) = ?', [mb_strtolower((string) $value, 'UTF-8')])
                        ->where('id', '!=', $asignatura->id)
                        ->exists();
                    if ($existe) {
                        $fail('Esta asignatura ya está registrada en el catálogo.');
                    }
                },
            ],
        ]);

        try {
            $asignatura->update(['nombre' => $request->nombre]);

            return back()->with('success', 'Asignatura actualizada correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un problema al intentar actualizar el nombre de la asignatura.'));
        }
    }

    public function destroy(Asignatura $asignatura)
    {
        try {
            $asignatura->delete();

            return back()->with('success', 'Asignatura eliminada del catálogo.');
        } catch (QueryException $e) {
            return back()->with('error', $this->formatearError($e, 'No puedes eliminar esta asignatura porque ya tiene calificaciones o asignaciones asociadas.'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'Ocurrió un error inesperado al intentar eliminar la asignatura.'));
        }
    }
}