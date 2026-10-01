<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AulaAsignaturaController extends Controller
{
    use AuthorizesRequests;

    // Agregar Materia Extra
    public function store(Request $request, Aula $aula)
    {
        // Validación AFUERA del try-catch para mantener vivos los mensajes del formulario
        $request->validate([
            'asignatura_id' => 'required|exists:asignatura,id',
            'horas_semanales' => 'required|integer|min:1|max:40',
        ]);

        try {
            $this->authorize('update', $aula);

            // Verificamos que no se intente agregar una materia que ya está en el aula
            $existe = AulaAsignaturaDocente::where('aula_id', $aula->id)
                                           ->where('asignatura_id', $request->asignatura_id)
                                           ->first();
            if ($existe) {
                return back()->with('error', 'Error: Esta materia ya está asignada a esta aula.');
            }

            AulaAsignaturaDocente::create([
                'aula_id' => $aula->id,
                'asignatura_id' => $request->asignatura_id,
                'docente_id' => null, // El profesor se asigna después
                'anio_escolar_id' => $aula->anio_escolar_id,
                'horas_semanales' => $request->horas_semanales,
                'activo' => true,
            ]);

            return back()->with('success', 'Materia extra agregada correctamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si la base de datos falla al intentar hacer el INSERT
            return back()->withInput()->with('error', 'Ocurrió un problema técnico al intentar agregar la materia extra. Intenta nuevamente.');
        }
    }

    // Asignar Profesor a una Materia
    public function update(Request $request, Aula $aula, AulaAsignaturaDocente $asignatura)
    {
        // Validación AFUERA del try-catch
        $request->validate([
            'docente_id' => 'required|exists:docente,id'
        ]);

        try {
            $this->authorize('update', $aula);

            // Actualizamos el registro de la tabla pivote
            $asignatura->update([
                'docente_id' => $request->docente_id
            ]);

            return back()->with('success', 'Profesor asignado correctamente a la materia.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si la base de datos falla al ejecutar el UPDATE
            return back()->with('error', 'Hubo un problema al asignar el profesor. Es posible que haya un fallo de conexión. Intenta de nuevo.');
        }
    }
}