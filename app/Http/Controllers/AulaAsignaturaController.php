<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Horario;
use App\Models\MallaCurricular;
use App\Models\AulaAsignaturaDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AulaAsignaturaController extends Controller
{
    use AuthorizesRequests;

    // Agregar Materia Extra
    public function store(Request $request, Aula $aula)
    {
        $request->validate([
            'asignatura_id' => 'required|exists:asignatura,id',
            'horas_semanales' => 'required|integer|min:1|max:40',
            'docente_id' => 'nullable|exists:docente,id',
        ]);

        try {
            $this->authorize('update', $aula);

            $existe = AulaAsignaturaDocente::where('aula_id', $aula->id)
                                           ->where('asignatura_id', $request->asignatura_id)
                                           ->first();
            if ($existe) {
                return back()->with('error', 'Error: Esta materia ya está asignada a esta aula.');
            }

            AulaAsignaturaDocente::create([
                'aula_id' => $aula->id,
                'asignatura_id' => $request->asignatura_id,
                'docente_id' => $request->docente_id ?: null,
                'anio_escolar_id' => $aula->anio_escolar_id,
                'horas_semanales' => $request->horas_semanales,
                'activo' => true,
            ]);

            return back()->with('success', 'Materia extraordinaria agregada correctamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'Ocurrió un problema técnico al intentar agregar la materia extra.');
        }
    }

    // Asignar o Cambiar Profesor a una Materia (con escudo anti-choque si ya tiene bloques en el horario)
    public function update(Request $request, Aula $aula, AulaAsignaturaDocente $asignatura)
    {
        $request->validate([
            'docente_id' => 'nullable|exists:docente,id'
        ]);

        try {
            $this->authorize('update', $aula);

            $nuevoDocenteId = $request->docente_id ?: null;

            // Si se está asignando un profesor y la materia ya tiene bloques en el horario, verificamos que no choque
            if ($nuevoDocenteId) {
                $bloquesActuales = Horario::where('aula_asignatura_docente_id', $asignatura->id)->get();

                foreach ($bloquesActuales as $bloqueClase) {
                    $choque = Horario::with('aulaAsignaturaDocente.aula.grado')
                        ->whereHas('aulaAsignaturaDocente', function ($q) use ($nuevoDocenteId, $asignatura) {
                            $q->where('docente_id', $nuevoDocenteId)
                              ->where('id', '!=', $asignatura->id);
                        })
                        ->where('dia_semana', $bloqueClase->dia_semana)
                        ->where('bloque_horario_id', $bloqueClase->bloque_horario_id)
                        ->first();

                    if ($choque) {
                        $gradoOcupado = $choque->aulaAsignaturaDocente->aula->grado->nombre ?? '';
                        $aulaOcupada = $choque->aulaAsignaturaDocente->aula->nombre ?? 'otra sección';
                        return back()->with('error', "¡Choque de Horario! No se puede asignar este docente porque ya imparte clases en {$gradoOcupado} - {$aulaOcupada} el día {$bloqueClase->dia_semana} en uno de los bloques de esta materia.");
                    }
                }
            }

            $asignatura->update([
                'docente_id' => $nuevoDocenteId
            ]);

            return back()->with('success', $nuevoDocenteId ? 'Profesor asignado correctamente a la materia.' : 'La materia quedó sin docente asignado.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->with('error', 'Hubo un problema al asignar el profesor. Intenta de nuevo.');
        }
    }

    // NUEVO: Asignación Masiva de un Docente a una Asignatura en múltiples Aulas/Grados
    public function asignarMasivo(Request $request)
    {
        $request->validate([
            'asignatura_id' => 'required|exists:asignatura,id',
            'docente_id'    => 'required|exists:docente,id',
            'aulas_ids'     => 'required|array|min:1',
            'aulas_ids.*'   => 'exists:aula,id',
        ], [
            'aulas_ids.required' => 'Debes seleccionar al menos un aula o sección para aplicar la asignación.',
        ]);

        DB::beginTransaction();
        try {
            $this->authorize('viewAny', Aula::class);

            $asignaturaId = (int) $request->asignatura_id;
            $docenteId = (int) $request->docente_id;
            $aulas = Aula::with('grado')->whereIn('id', $request->aulas_ids)->get();

            $actualizadas = 0;
            $omitidasPorChoque = [];

            foreach ($aulas as $aula) {
                $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)
                    ->where('asignatura_id', $asignaturaId)
                    ->first();

                // Si la materia aún no está en el aula, verificamos si existe en la Malla de ese grado para crearla
                if (!$asignacion) {
                    $itemMalla = MallaCurricular::where('grado_id', $aula->grado_id)
                        ->where('asignatura_id', $asignaturaId)
                        ->first();

                    $horasSugeridas = $itemMalla ? $itemMalla->horas_semanales_sugeridas : 2;

                    $asignacion = AulaAsignaturaDocente::create([
                        'aula_id'         => $aula->id,
                        'asignatura_id'   => $asignaturaId,
                        'docente_id'      => $docenteId,
                        'anio_escolar_id' => $aula->anio_escolar_id,
                        'horas_semanales' => $horasSugeridas,
                        'activo'          => true,
                    ]);
                    $actualizadas++;
                    continue;
                }

                // Si la asignación ya tenía bloques horarios armados, verificamos que el nuevo docente no choque
                $bloquesActuales = Horario::where('aula_asignatura_docente_id', $asignacion->id)->get();
                $tieneChoque = false;

                foreach ($bloquesActuales as $bloqueClase) {
                    $choque = Horario::whereHas('aulaAsignaturaDocente', function ($q) use ($docenteId, $asignacion) {
                            $q->where('docente_id', $docenteId)
                              ->where('id', '!=', $asignacion->id);
                        })
                        ->where('dia_semana', $bloqueClase->dia_semana)
                        ->where('bloque_horario_id', $bloqueClase->bloque_horario_id)
                        ->exists();

                    if ($choque) {
                        $tieneChoque = true;
                        break;
                    }
                }

                if ($tieneChoque) {
                    $omitidasPorChoque[] = "{$aula->grado->nombre} {$aula->nombre}";
                    continue;
                }

                $asignacion->update(['docente_id' => $docenteId]);
                $actualizadas++;
            }

            DB::commit();

            if (count($omitidasPorChoque) > 0) {
                $lista = implode(', ', $omitidasPorChoque);
                return back()->with('error', "Se asignó el docente en {$actualizadas} aula(s), pero se omitió en ({$lista}) porque ya tenía choque de horario en esos bloques.");
            }

            return back()->with('success', "¡Asignación masiva completada! El docente fue asignado a {$actualizadas} sección(es) exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'Ocurrió un error al procesar la asignación masiva.');
        }
    }
}