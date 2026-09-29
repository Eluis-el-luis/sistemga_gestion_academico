<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\Usuario $user */
        $user = auth()->user();
        
        // Bloqueo directo por rol en lugar de usar un Policy
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'No tienes permiso para modificar el catálogo del currículo.');
        }

        try {
            $asignaturas = Asignatura::orderBy('nombre', 'asc')->get();
            
            return view('academico.asignaturas.index', compact('asignaturas'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla la conexión a la base de datos al cargar el catálogo
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error inesperado al cargar el catálogo de asignaturas.');
        }
    }

    public function store(Request $request)
    {
        // Limpiamos espacios vacíos accidentales al inicio o al final
        $request->merge(['nombre' => trim($request->nombre)]);

        // Validación AFUERA del try-catch para no interferir con las advertencias rojas de la vista
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    // Verificación insensible a mayúsculas/minúsculas para PostgreSQL
                    $existe = \App\Models\Asignatura::whereRaw('LOWER(nombre) = ?', [mb_strtolower($value, 'UTF-8')])->exists();
                    if ($existe) {
                        $fail('Esta asignatura ya está registrada en el catálogo.');
                    }
                },
            ]
        ]);

        try {
            Asignatura::create(['nombre' => $request->nombre]);

            return back()->with('success', 'Asignatura registrada correctamente.');

        } catch (\Exception $e) {
            // CONTINGENCIA: Si el servidor falla justo al momento de hacer el INSERT
            return back()->withInput()->with('error', 'Hubo un problema técnico al registrar la asignatura. Por favor, intenta de nuevo.');
        }
    }

    public function update(Request $request, Asignatura $asignatura)
    {
        $request->merge(['nombre' => trim($request->nombre)]);

        // Validación AFUERA del try-catch
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($asignatura) {
                    // Verificamos que no exista otro registro con el mismo nombre (ignorando mayúsculas) excluyendo el ID actual
                    $existe = \App\Models\Asignatura::whereRaw('LOWER(nombre) = ?', [mb_strtolower($value, 'UTF-8')])
                        ->where('id', '!=', $asignatura->id)
                        ->exists();
                    
                    if ($existe) {
                        $fail('Esta asignatura ya está registrada en el catálogo.');
                    }
                },
            ]
        ]);

        try {
            $asignatura->update(['nombre' => $request->nombre]);

            return back()->with('success', 'Asignatura actualizada correctamente.');

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla el UPDATE en base de datos
            return back()->withInput()->with('error', 'Ocurrió un problema al intentar actualizar el nombre de la asignatura.');
        }
    }

    public function destroy(Asignatura $asignatura)
    {
        try {
            $asignatura->delete();
            return back()->with('success', 'Asignatura eliminada del catálogo.');
            
        } catch (\Illuminate\Database\QueryException $e) {
            // EXCEPCIÓN ESPECÍFICA: Atrapa problemas de integridad referencial (llaves foráneas)
            return back()->with('error', 'No puedes eliminar esta asignatura porque ya tiene calificaciones o está asignada a un docente.');
            
        } catch (\Exception $e) {
            // CONTINGENCIA GENERAL: Para cualquier otro tipo de fallo en el sistema
            return back()->with('error', 'Ocurrió un error inesperado al intentar eliminar la asignatura.');
        }
    }
}