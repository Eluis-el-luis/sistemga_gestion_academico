<?php

namespace App\Http\Controllers;

use App\Models\IncidenciaDisciplinaria;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidenciaDisciplinariaController extends Controller
{
    public function index()
    {
        try {
            $incidencias = IncidenciaDisciplinaria::with([
                'matricula.alumno',
                'matricula.aula.grado',
                'docenteReporta.usuario',
                'coordinadorAtiende',
            ])
                ->orderByRaw("
                    CASE
                        WHEN estado = 'Reportada' THEN 1
                        WHEN estado = 'Citación a Padres' THEN 2
                        WHEN estado = 'En Revisión' THEN 3
                        ELSE 4
                    END
                ")
                ->orderByDesc('created_at')
                ->get();

            return view('academico.disciplina.index', compact('incidencias'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al cargar el buzón de incidencias disciplinarias.'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula_id' => ['required', 'integer', 'exists:matricula,id'],
            'nivel_falta' => ['required', 'in:Leve,Grave,Muy Grave'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'fecha_incidencia' => ['required', 'date'],
        ]);

        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $docente = $usuario->docente;

            if (!$docente) {
                return back()->with('error', 'Su usuario no tiene un perfil de docente asociado para reportar incidencias.');
            }

            IncidenciaDisciplinaria::create([
                'matricula_id' => $request->matricula_id,
                'docente_reporta_id' => $docente->id,
                'nivel_falta' => $request->nivel_falta,
                'descripcion' => $request->descripcion,
                'fecha_incidencia' => $request->fecha_incidencia,
                'estado' => 'Reportada',
            ]);

            return back()->with('success', 'Incidencia reportada exitosamente al Coordinador.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un error técnico al reportar la incidencia.'));
        }
    }

    public function update(Request $request, IncidenciaDisciplinaria $incidencia)
    {
        $request->validate([
            'estado' => ['required', 'in:Reportada,En Revisión,Citación a Padres,Cerrada'],
            'fecha_citacion_padres' => ['nullable', 'date'],
            'resolucion_final' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $incidencia->update([
                'estado' => $request->estado,
                'coordinador_atiende_id' => Auth::id(),
                'fecha_citacion_padres' => $request->fecha_citacion_padres,
                'resolucion_final' => $request->resolucion_final,
            ]);

            return back()->with('success', 'Estado de la incidencia actualizado correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'No se pudo actualizar el estado de la incidencia.'));
        }
    }
}