<?php

namespace App\Services;

use App\Models\AnioEscolar;
use App\Models\Nota;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AnioEscolarService
{
    public const ERROR_CORTES_NO_CERRADOS = 'cortes_no_cerrados';
    public const ERROR_PROMEDIOS_NO_GENERADOS = 'promedios_no_generados';
    public const ERROR_ESTADO_ESTUDIANTES_INDEFINIDO = 'estado_estudiantes_indefinido';
    public const ERROR_YA_EXISTE_ACTIVO = 'ya_existe_activo';
    public const ERROR_ESTADO_INVALIDO = 'estado_invalido';
    public const ERROR_YA_CERRADO = 'ya_cerrado';
    public const ERROR_NO_ES_ACTIVO = 'no_es_activo';

    public function activar(AnioEscolar $anioEscolar, ?Usuario $usuario = null): AnioEscolar
    {
        if ($anioEscolar->es_cerrado) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede activar un año escolar cerrado.',
            ]);
        }

        if ($anioEscolar->es_activo) {
            return $anioEscolar;
        }

        if (!$anioEscolar->es_planificacion) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se puede activar un año en estado "Planificación".',
            ]);
        }

        try {
            return DB::transaction(function () use ($anioEscolar, $usuario) {$anioActivoActual = AnioEscolar::obtenerActivo();

                if ($anioActivoActual && (int)$anioActivoActual->id !== (int) $anioEscolar->id) {$anioActivoActual->estado = AnioEscolar::ESTADO_PLANIFICACION;
                    $anioActivoActual->save();
                }

                $anioEscolar->estado = AnioEscolar::ESTADO_ACTIVO;
                $anioEscolar->save();

                $this->registrarAuditoria($anioEscolar,
                    'activado',
                    $usuario,
                    'Año escolar activado. Año anterior (si existía) pasado a Planificación.'
                );

                return $anioEscolar->fresh();
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al activar año escolar', [
                'anio_escolar_id' => $anioEscolar->id,
                'usuario_id' => $usuario?->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible activar el año escolar.', 0, $e);
        }
    }

    public function pasarAPlanificacion(AnioEscolar $anioEscolar, ?Usuario $usuario = null): AnioEscolar
    {
        if ($anioEscolar->es_cerrado) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede cambiar un año escolar cerrado a Planificación.',
            ]);
        }

        if ($anioEscolar->es_planificacion) {
            return $anioEscolar;
        }

        if (!$anioEscolar->es_activo) {
            throw ValidationException::withMessages([
                'estado' => 'Solo el año activo puede pasar a Planificación.',
            ]);
        }

        try {
            return DB::transaction(function () use ($anioEscolar, $usuario) {$anioEscolar->estado = AnioEscolar::ESTADO_PLANIFICACION;
                $anioEscolar->save();

                $this->registrarAuditoria($anioEscolar,
                    'pasado_a_planificacion',
                    $usuario,
                    'Año escolar pasado a Planificación desde Activo.'
                );

                return $anioEscolar->fresh();
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al pasar año escolar a planificación', [
                'anio_escolar_id' => $anioEscolar->id,
                'usuario_id' => $usuario?->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible cambiar el año escolar a Planificación.', 0, $e);
        }
    }

    public function cerrar(AnioEscolar $anioEscolar, Usuario $usuario, array$data = []): AnioEscolar
    {
        if ($anioEscolar->es_cerrado) {
            throw ValidationException::withMessages([
                'estado' => 'El año escolar ya está cerrado.',
            ]);
        }

        if (!$anioEscolar->es_activo) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se puede cerrar un año escolar que esté Activo.',
            ]);
        }

        $this->validarCierreCompleto($anioEscolar);

        try {
            return DB::transaction(function () use ($anioEscolar,$usuario, $data) {$anioEscolar->estado = AnioEscolar::ESTADO_CERRADO;
                $anioEscolar->fecha_cierre = now();
                $anioEscolar->cerrado_por =$usuario->id;
                $anioEscolar->observaciones_cierre =$data['observaciones_cierre'] ?? null;
                $anioEscolar->save();

                $this->registrarAuditoria($anioEscolar,
                    'cerrado',
                    $usuario,
                    'Año escolar cerrado definitivamente.',
                    [
                        'observaciones' => $data['observaciones_cierre'] ?? null,
                        'validaciones_pasadas' => [
                            'cortes_cerrados' => true,
                            'promedios_generados' => true,
                            'estudiantes_con_estado_final' => true,
                        ],
                    ]
                );

                return $anioEscolar->fresh();
            });
        } catch (\Throwable $e) {
            Log::error('Error al cerrar año escolar', [
                'anio_escolar_id' => $anioEscolar->id,
                'usuario_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible completar el cierre del año escolar.', 0, $e);
        }
    }

    public function validarCierreCompleto(AnioEscolar $anioEscolar): void
    {
        $errores = [];

        if (!$this->validarTodosLosCortesCerrados($anioEscolar)) {$asignacionesPendientes = $anioEscolar->getAsignacionesConCortesPendientes();$detalle = $asignacionesPendientes->map(function ($a) {
                $docenteNombre =$a->docente?->usuario?->nombre_completo
                    ?? $a->docente?->usuario?->name
                    ?? 'Sin docente';

                return ($a->asignatura?->nombre ?? 'Sin asignatura')
                    . ' - ' . $docenteNombre
                    . ' (' . ($a->aula?->nombre ?? 'Sin aula') . ')';
            })->implode('; ');

            $errores[] = [
                'codigo' => self::ERROR_CORTES_NO_CERRADOS,
                'mensaje' => 'No todos los docentes han cerrado sus actas de calificaciones.',
                'detalle' => $detalle !== '' ?$detalle : 'Hay asignaciones sin cerrar en uno o más cortes evaluativos.',
            ];
        }

        if (!$this->validarPromediosFinalesGenerados($anioEscolar)) {$errores[] = [
                'codigo' => self::ERROR_PROMEDIOS_NO_GENERADOS,
                'mensaje' => 'No todos los promedios finales definitivos han sido generados.',
                'detalle' => 'Hay estudiantes con registros finales faltantes en alguna asignatura o modalidad.',
            ];
        }

        if (!$this->validarEstadosFinalesEstudiantes($anioEscolar)) {$errores[] = [
                'codigo' => self::ERROR_ESTADO_ESTUDIANTES_INDEFINIDO,
                'mensaje' => 'No todos los estudiantes tienen su estado final definido.',
                'detalle' => 'Hay estudiantes sin registro final o sin examen de reparación definido.',
            ];
        }

        if ($errores !== []) {
            throw ValidationException::withMessages([
                'validacion_cierre' => $errores,
            ]);
        }
    }

    protected function validarTodosLosCortesCerrados(AnioEscolar $anioEscolar): bool
    {
        return $anioEscolar->getTodosLosCortesCerrados();
    }

    protected function validarPromediosFinalesGenerados(AnioEscolar $anioEscolar): bool
    {
        $matriculasActivas =$anioEscolar->matriculas()
            ->where('estado', 'activo')
            ->with(['aula.modalidad', 'aula.asignaciones' => fn ($q) =>$q->where('activo', true)])
            ->get();

        if ($matriculasActivas->isEmpty()) {
            return false;
        }

        foreach ($matriculasActivas as $matricula) {$esPreescolar = str_contains(
                mb_strtolower((string) optional($matricula->aula?->modalidad)->nombre, 'UTF-8'),
                'preescolar'
            );

            foreach ($matricula->aula?->asignaciones ?? [] as $asignacion) {
                $notaFinal = Nota::where('matricula_id', $matricula->id)
                    ->where('aula_asignatura_docente_id', $asignacion->id)
                    ->whereHas('corteEvaluativo', fn ($q) =>$q->where('numero', 4))
                    ->first();

                if (!$notaFinal) {
                    return false;
                }

                if ($esPreescolar) {
                    if (is_null($notaFinal->indicador_logro_id) && is_null($notaFinal->porcentaje_avance)) {
                        return false;
                    }
                    continue;
                }

                if (is_null($notaFinal->nota_cuantitativa)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function validarEstadosFinalesEstudiantes(AnioEscolar $anioEscolar): bool
    {
        return $anioEscolar->getTodosEstudiantesConEstadoFinal();
    }

    public function obtenerReporteValidacionesCierre(AnioEscolar $anioEscolar): array
    {
        $cortesCerrados = $this->validarTodosLosCortesCerrados($anioEscolar);
        $promediosGenerados =$this->validarPromediosFinalesGenerados($anioEscolar);$estadosDefinidos = $this->validarEstadosFinalesEstudiantes($anioEscolar);
        $asignacionesPendientes =$anioEscolar->getAsignacionesConCortesPendientes();

        return [
            'puede_cerrar' => $cortesCerrados && $promediosGenerados &&$estadosDefinidos,
            'validaciones' => [
                'cortes_cerrados' => [
                    'cumple' => $cortesCerrados,
                    'mensaje' => $cortesCerrados
                        ? 'Todos los cortes evaluativos están cerrados por todos los docentes.'
                        : 'Faltan cortes por cerrar: ' . $asignacionesPendientes->count() . ' asignaciones pendientes.',
                    'detalle' => $asignacionesPendientes->map(fn ($a) => [
                        'asignatura' => $a->asignatura?->nombre ?? 'Sin asignatura',
                        'docente' => $a->docente?->usuario?->nombre_completo ?? ($a->docente?->usuario?->name ?? 'Sin docente'),
                        'aula' => $a->aula?->nombre ?? 'Sin aula',
                    ])->values()->toArray(),
                ],
                'promedios_generados' => [
                    'cumple' => $promediosGenerados,
                    'mensaje' => $promediosGenerados
                        ? 'Todos los promedios finales están generados.'
                        : 'Hay estudiantes sin registro final en alguna asignatura (IV Corte).',
                ],
                'estados_estudiantes_definidos' => [
                    'cumple' => $estadosDefinidos,
                    'mensaje' => $estadosDefinidos
                        ? 'Todos los estudiantes tienen estado final definido.'
                        : 'Hay estudiantes sin estado final (falta registro o examen de reparación).',
                ],
            ],
            'resumen_estadistico' => $anioEscolar->getResumenEstadistico(),
        ];
    }

    public function crear(array $data, ?Usuario $usuario = null): AnioEscolar
    {
        try {
            return DB::transaction(function () use ($data, $usuario) {$anioEscolar = AnioEscolar::create([
                    'nombre' => $data['nombre'],
                    'fecha_inicio' => $data['fecha_inicio'],
                    'fecha_fin' => $data['fecha_fin'],
                    'estado' => AnioEscolar::ESTADO_PLANIFICACION,
                ]);

                $this->registrarAuditoria($anioEscolar,
                    'creado',
                    $usuario,
                    'Año escolar creado en estado Planificación.'
                );

                return $anioEscolar;
            });
        } catch (\Throwable $e) {
            Log::error('Error al crear año escolar', [
                'usuario_id' => $usuario?->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible crear el año escolar.', 0, $e);
        }
    }

    public function actualizar(AnioEscolar $anioEscolar, array $data, ?Usuario $usuario = null): AnioEscolar
    {
        if ($anioEscolar->es_cerrado) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede modificar un año escolar cerrado.',
            ]);
        }

        try {
            $anioEscolar->fill($data);$anioEscolar->save();

            $this->registrarAuditoria($anioEscolar,
                'actualizado',
                $usuario,
                'Datos del año escolar actualizados.'
            );

            return $anioEscolar->fresh();
        } catch (\Throwable $e) {
            Log::error('Error al actualizar año escolar', [
                'anio_escolar_id' => $anioEscolar->id,
                'usuario_id' => $usuario?->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible actualizar el año escolar.', 0, $e);
        }
    }

    public function eliminar(AnioEscolar $anioEscolar, ?Usuario $usuario = null): bool
    {
        if (!$anioEscolar->es_planificacion) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden eliminar años escolares en estado Planificación.',
            ]);
        }

        $tieneDependencias =$anioEscolar->aulas()->exists()
            || $anioEscolar->matriculas()->exists()
            || $anioEscolar->asignaciones()->exists()
            || $anioEscolar->cortesEvaluativos()->exists();

        if ($tieneDependencias) {
            throw ValidationException::withMessages([
                'dependencias' => 'No se puede eliminar: el año escolar tiene aulas, matrículas, asignaciones o cortes evaluativos asociados.',
            ]);
        }

        try {
            $this->registrarAuditoria($anioEscolar,
                'eliminado',
                $usuario,
                'Año escolar eliminado (estaba en Planificación sin dependencias).'
            );

            return (bool) $anioEscolar->delete();
        } catch (\Throwable $e) {
            Log::error('Error al eliminar año escolar', [
                'anio_escolar_id' => $anioEscolar->id,
                'usuario_id' => $usuario?->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible eliminar el año escolar.', 0, $e);
        }
    }

    public function obtenerOcrearActivo(?Usuario $usuario = null): AnioEscolar
    {
        $activo = AnioEscolar::obtenerActivo();
        if ($activo) {
            return $activo;
        }

        $enPlanificacion = AnioEscolar::planificacion()->first();
        if ($enPlanificacion) {
            return $this->activar($enPlanificacion,$usuario);
        }

        if (app()->environment('local', 'testing')) {
            $anio = now()->year;

            return $this->crear([
                'nombre' => "{$anio}-" . ($anio + 1),
                'fecha_inicio' => "{$anio}-02-01",
                'fecha_fin' => ($anio + 1) . '-11-30', ],$usuario);
        }

        throw new \RuntimeException('No hay año escolar activo ni en planificación para activar.');
    }

    protected function registrarAuditoria(
        AnioEscolar $anioEscolar,
        string $accion,
        ?Usuario $usuario,
        string $descripcion,
        array $metadata = []
    ): void {
        try {
            Log::channel('auditoria')->info("AnioEscolar: {$accion}", array_merge([
                'anio_escolar_id' => $anioEscolar->id,
                'anio_escolar_nombre' => $anioEscolar->nombre,
                'accion' => $accion,
                'usuario_id' => $usuario?->id,
                'usuario_nombre' => $usuario?->nombre_completo ?? $usuario?->name,
                'descripcion' => $descripcion,
                'fecha' => now()->toISOString(),
            ], $metadata));
        } catch (\Throwable) {
            Log::info("AnioEscolar: {$accion}", [
                'anio_escolar_id' => $anioEscolar->id,
                'usuario_id' => $usuario?->id,
                'descripcion' => $descripcion,
            ]);
        }
    }
}