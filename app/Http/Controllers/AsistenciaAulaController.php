<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarAsistenciaAulaRequest;
use App\Models\AsistenciaAsignatura;
use App\Models\AsistenciaAula;
use App\Models\Aula;
use App\Models\Matricula;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsistenciaAulaController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request)
    {
        try {
            $this->authorize('create', AsistenciaAula::class);

            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $docente = $usuario->docente;

            if (!$docente) {
                return redirect()->route('dashboard')->with('error', 'Su usuario no tiene un perfil de docente asociado.');
            }

            $aula = Aula::where('docente_guia_id', $docente->id)
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tiene un aula asignada como Docente Guía en el ciclo actual.');
            }

            $fecha = $request->query('fecha', Carbon::today()->toDateString());
            $matriculas = Matricula::with('alumno')
                ->where('aula_id', $aula->id)
                ->where('estado', 'activo')
                ->get();

            $asistenciasPrevias = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
                ->where('fecha', $fecha)
                ->get()
                ->keyBy('matricula_id');

            $incidenciasHoy = AsistenciaAsignatura::with('asignatura')
                ->whereIn('matricula_id', $matriculas->pluck('id'))
                ->where('fecha', $fecha)
                ->get();

            return view('academico.asistencia.aula.create', compact('aula', 'matriculas', 'fecha', 'asistenciasPrevias', 'incidenciasHoy'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error al cargar la planilla de asistencia.'));
        }
    }

    public function store(GuardarAsistenciaAulaRequest $request)
    {
        $datos = $request->validated();
        $fecha = $datos['fecha'];

        try {
            $this->authorize('create', AsistenciaAula::class);

            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $docente = $usuario->docente;

            if (!$docente) {
                return back()->with('error', 'Su usuario no tiene un perfil de docente asociado.');
            }

            $aula = Aula::where('docente_guia_id', $docente->id)
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->first();

            if (!$aula) {
                return back()->with('error', 'No tiene un aula asignada como Docente Guía en el ciclo actual.');
            }

            $matriculaIdsValidos = Matricula::where('aula_id', $aula->id)
                ->where('estado', 'activo')
                ->pluck('id')
                ->all();

            foreach ($datos['asistencias'] as $asistencia) {
                if (!in_array((int) $asistencia['matricula_id'], $matriculaIdsValidos, true)) {
                    return back()->with('error', 'Intento de registrar asistencia para matrícula no autorizada.');
                }

                AsistenciaAula::updateOrCreate(
                    [
                        'matricula_id' => $asistencia['matricula_id'],
                        'fecha' => $fecha,
                    ],
                    [
                        'estado_asistencia' => $asistencia['estado_asistencia'],
                    ]
                );
            }

            return back()->with('success', 'Asistencia del aula registrada/actualizada exitosamente para la fecha: ' . Carbon::parse($fecha)->format('d/m/Y'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'Hubo un problema al guardar la asistencia masiva.'));
        }
    }
}