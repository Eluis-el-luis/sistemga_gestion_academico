<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Horario;
use App\Models\MallaCurricular;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AulaAsignaturaController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Aula $aula)
    {
        $request->validate([
            'asignatura_id' => ['required', 'integer', 'exists:asignatura,id'],
            'horas_semanales' => ['required', 'integer', 'min:1', 'max:40'],
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
        ]);

        try {
            $this->authorize('update', $aula);

            $existe = AulaAsignaturaDocente::where('aula_id', $aula->id)
                ->where('asignatura_id', $request->asignatura_id)
                ->exists();

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
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un problema técnico al intentar agregar la materia extra.'));
        }
    }

    public function update(Request $request, Aula $aula, AulaAsignaturaDocente $asignatura)
    {
        $request->validate([
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
        ]);

        try {
            $this->authorize('update', $aula);

            $nuevoDocenteId = $request->docente_id ? (int) $request->docente_id : null;

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

                        return back()->with('error', "¡Choque de Horario! El docente ya imparte clases en {$gradoOcupado} - {$aulaOcupada} el día {$bloqueClase->dia_semana} en uno de los bloques de esta materia.");
                    }
                }
            }

            $asignatura->update(['docente_id' => $nuevoDocenteId]);

            return back()->with('success', $nuevoDocenteId ? 'Profesor asignado correctamente a la materia.' : 'La materia quedó sin docente asignado.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'Hubo un problema al asignar el profesor.'));
        }
    }

    public function asignarMasivo(Request $request)
    {
        $request->validate([
            'asignatura_id' => ['required', 'integer', 'exists:asignatura,id'],
            'docente_id' => ['required', 'integer', 'exists:docente,id'],
            'aulas_ids' => ['required', 'array', 'min:1'],
            'aulas_ids.*' => ['integer', 'exists:aula,id'],
        ], [
            'aulas_ids.required' => 'Debes seleccionar al menos un aula o sección para aplicar la asignación.',
        ]);

        $this->authorize('create', AulaAsignaturaDocente::class);

        DB::beginTransaction();
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $docente = $usuario->docente;
            $aulasIds = $request->aulas_ids;

            if ($docente && !$usuario->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
                $aulaGuia = Aula::where('docente_guia_id', $docente->id)
                    ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                    ->first();

                if (!$aulaGuia) {
                    DB::rollBack();

                    return back()->with('error', 'No tiene un aula asignada como Guía en el ciclo actual.');
                }

                $aulasIds = [$aulaGuia->id];
            }

            $asignaturaId = (int) $request->asignatura_id;
            $docenteId = (int) $request->docente_id;
            $aulas = Aula::with('grado')->whereIn('id', $aulasIds)->get();

            $actualizadas = 0;
            $omitidasPorChoque = [];

            foreach ($aulas as $aula) {
                $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)
                    ->where('asignatura_id', $asignaturaId)
                    ->first();

                if (!$asignacion) {
                    $itemMalla = MallaCurricular::where('grado_id', $aula->grado_id)
                        ->where('asignatura_id', $asignaturaId)
                        ->first();

                    AulaAsignaturaDocente::create([
                        'aula_id' => $aula->id,
                        'asignatura_id' => $asignaturaId,
                        'docente_id' => $docenteId,
                        'anio_escolar_id' => $aula->anio_escolar_id,
                        'horas_semanales' => $itemMalla?->horas_semanales_sugeridas ?? 2,
                        'activo' => true,
                    ]);
                    $actualizadas++;
                    continue;
                }

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

            if ($omitidasPorChoque !== []) {
                $lista = implode(', ', $omitidasPorChoque);

                return back()->with('error', "Se asignó el docente en {$actualizadas} aula(s), pero se omitió en ({$lista}) por choque de horario.");
            }

            return back()->with('success', "¡Asignación masiva completada! El docente fue asignado a {$actualizadas} sección(es) exitosamente.");
        } catch (AuthorizationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un error al procesar la asignación masiva.'));
        }
    }
}