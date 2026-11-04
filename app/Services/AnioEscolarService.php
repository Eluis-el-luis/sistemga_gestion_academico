<?php

namespace App\Services;

use App\Models\AnioEscolar;
use App\Models\AulaAsignaturaDocente;
use App\Models\CorteCerrado;
use App\Models\CorteEvaluativo;
use App\Models\ExamenReparacion;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Servicio para la gestión del Año Escolar.
 * 
 * Encapsula la lógica de negocio estricta para:
 * - Restricción de "un solo año activo"
 * - Validación completa del proceso de cierre
 * - Transiciones de estado controladas
 */
class AnioEscolarService
{
    /**
     * Excepción personalizada para errores de validación de cierre
     */
    public const ERROR_CORTES_NO_CERRADOS = 'cortes_no_cerrados';
    public const ERROR_PROMEDIOS_NO_GENERADOS = 'promedios_no_generados';
    public const ERROR_ESTADO_ESTUDIANTES_INDEFINIDO = 'estado_estudiantes_indefinido';
    public const ERROR_YA_EXISTE_ACTIVO = 'ya_existe_activo';
    public const ERROR_ESTADO_INVALIDO = 'estado_invalido';
    public const ERROR_YA_CERRADO = 'ya_cerrado';
    public const ERROR_NO_ES_ACTIVO = 'no_es_activo';

    /**
     * Activa un año escolar.
     * 
     * Reglas:
     * - Solo puede haber UN año escolar activo a la vez
     * - El año debe estar en estado "planificacion"
     * - Desactiva el año activo anterior si existe (cambia a planificación)
     * 
     * @throws ValidationException Si ya existe un año activo y no se puede cambiar
     */
    public function activar(AnioEscolar $anioEscolar, ?Usuario $usuario = null): AnioEscolar
    {
        // Validar que no esté cerrado
        if ($anioEscolar->es_cerrado) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede activar un año escolar cerrado.',
            ]);
        }

        // Si ya es activo, no hay nada que hacer
        if ($anioEscolar->es_activo) {
            return $anioEscolar;
        }

        // Validar que esté en planificación
        if (!$anioEscolar->es_planificacion) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se puede activar un año en estado "Planificación".',
            ]);
        }

        return DB::transaction(function () use ($anioEscolar, $usuario) {
            // Si hay un año activo, cambiarlo a planificación
            $anioActivoActual = AnioEscolar::obtenerActivo();
            
            if ($anioActivoActual && $anioActivoActual->id !== $anioEscolar->id) {
                $anioActivoActual->estado = AnioEscolar::ESTADO_PLANIFICACION;
                $anioActivoActual->save();
            }

            // Activar el nuevo año
            $anioEscolar->estado = AnioEscolar::ESTADO_ACTIVO;
            $anioEscolar->save();

            // Registrar en log de auditoría
            $this->registrarAuditoria(
                $anioEscolar,
                'activado',
                $usuario,
                "Año escolar activado. Año anterior (si existía) pasado a Planificación."
            );

            return $anioEscolar->fresh();
        });
    }

    /**
     * Cambia un año escolar a Planificación.
     * 
     * Reglas:
     * - Solo si está en "activo" y NO hay otro año que deba estar activo
     * - No se puede desde "cerrado"
     */
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

        return DB::transaction(function () use ($anioEscolar, $usuario) {
            $anioEscolar->estado = AnioEscolar::ESTADO_PLANIFICACION;
            $anioEscolar->save();

            $this->registrarAuditoria(
                $anioEscolar,
                'pasado_a_planificacion',
                $usuario,
                "Año escolar pasado a Planificación desde Activo."
            );

            return $anioEscolar->fresh();
        });
    }

    /**
     * Cierra un año escolar con validación estricta completa.
     * 
     * Validaciones obligatorias:
     * 1. Todos los docentes han subido y cerrado sus actas de calificaciones (todos los cortes)
     * 2. Los promedios finales definitivos están generados
     * 3. El estado final de los estudiantes (Aprobado/Reprobado/Reparación) está definido
     * 
     * @param AnioEscolar $anioEscolar Año a cerrar (debe estar activo)
     * @param Usuario $usuario Usuario que ejecuta el cierre
     * @param array $data Datos adicionales (observaciones_cierre)
     * 
     * @throws ValidationException Si alguna validación falla
     */
    public function cerrar(AnioEscolar $anioEscolar, Usuario $usuario, array $data = []): AnioEscolar
    {
        // Validaciones previas de estado
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

        // Ejecutar validaciones estrictas de cierre
        $this->validarCierreCompleto($anioEscolar);

        return DB::transaction(function () use ($anioEscolar, $usuario, $data) {
            $anioEscolar->estado = AnioEscolar::ESTADO_CERRADO;
            $anioEscolar->fecha_cierre = now();
            $anioEscolar->cerrado_por = $usuario->id;
            $anioEscolar->observaciones_cierre = $data['observaciones_cierre'] ?? null;
            $anioEscolar->save();

            $this->registrarAuditoria(
                $anioEscolar,
                'cerrado',
                $usuario,
                "Año escolar cerrado definitivamente. Validaciones: cortes cerrados, promedios generados, estados de estudiantes definidos.",
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
    }

    /**
     * Valida todas las condiciones obligatorias para cerrar un año escolar.
     * 
     * @throws ValidationException Con mensajes detallados de qué validaciones fallaron
     */
    public function validarCierreCompleto(AnioEscolar $anioEscolar): void
    {
        $errores = [];

        // 1. Validar que todos los cortes estén cerrados por todos los docentes
        if (!$this->validarTodosLosCortesCerrados($anioEscolar)) {
            $asignacionesPendientes = $anioEscolar->getAsignacionesConCortesPendientes();
            $detalle = $asignacionesPendientes->map(function ($a) {
                return "{$a->asignatura->nombre} - {$a->docente->usuario->name} ({$a->aula->nombre})";
            })->implode('; ');
            
            $errores[] = [
                'codigo' => self::ERROR_CORTES_NO_CERRADOS,
                'mensaje' => 'No todos los docentes han cerrado sus actas de calificaciones.',
                'detalle' => $detalle ?: 'Hay asignaciones sin cerrar en uno o más cortes evaluativos.',
            ];
        }

        // 2. Validar que los promedios finales estén generados
        if (!$this->validarPromediosFinalesGenerados($anioEscolar)) {
            $errores[] = [
                'codigo' => self::ERROR_PROMEDIOS_NO_GENERADOS,
                'mensaje' => 'No todos los promedios finales definitivos han sido generados.',
                'detalle' => 'Hay estudiantes con notas finales faltantes en alguna asignatura.',
            ];
        }

        // 3. Validar que todos los estudiantes tengan estado final definido
        if (!$this->validarEstadosFinalesEstudiantes($anioEscolar)) {
            $errores[] = [
                'codigo' => self::ERROR_ESTADO_ESTUDIANTES_INDEFINIDO,
                'mensaje' => 'No todos los estudiantes tienen su estado final definido (Aprobado/Reprobado/Reparación).',
                'detalle' => 'Hay estudiantes sin nota final en alguna asignatura o sin examen de reparación definido para materias reprobadas.',
            ];
        }

        if (!empty($errores)) {
            throw ValidationException::withMessages([
                'validacion_cierre' => $errores,
            ]);
        }
    }

    /**
     * Valida que todos los cortes estén cerrados por todas las asignaciones activas.
     */
    protected function validarTodosLosCortesCerrados(AnioEscolar $anioEscolar): bool
    {
        return $anioEscolar->getTodosLosCortesCerrados();
    }

    /**
     * Valida que todos los estudiantes tengan promedios finales generados.
     * Verifica que cada matrícula activa tenga nota final (corte 4) en todas sus asignaturas.
     */
    protected function validarPromediosFinalesGenerados(AnioEscolar $anioEscolar): bool
    {
        $matriculasActivas = $anioEscolar->matriculas()
            ->where('estado', 'activo')
            ->with(['aula.asignaciones' => fn($q) => $q->where('activo', true)])
            ->get();

        if ($matriculasActivas->isEmpty()) {
            return false;
        }

        foreach ($matriculasActivas as $matricula) {
            $asignaciones = $matricula->aula->asignaciones;
            
            foreach ($asignaciones as $asignacion) {
                // Buscar nota del IV corte (número 4)
                $notaFinal = Nota::where('matricula_id', $matricula->id)
                    ->where('aula_asignatura_docente_id', $asignacion->id)
                    ->whereHas('corteEvaluativo', fn($q) => $q->where('numero', 4))
                    ->first();

                if (!$notaFinal || is_null($notaFinal->nota_cuantitativa)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Valida que todos los estudiantes tengan estado final definido.
     * Estado final = Aprobado (nota >= 60) / Reprobado (nota < 60 sin reparación) / Reparación (nota < 60 con examen)
     */
    protected function validarEstadosFinalesEstudiantes(AnioEscolar $anioEscolar): bool
    {
        return $anioEscolar->getTodosEstudiantesConEstadoFinal();
    }

    /**
     * Obtiene un reporte detallado de validaciones para el cierre (para UI).
     */
    public function obtenerReporteValidacionesCierre(AnioEscolar $anioEscolar): array
    {
        $cortesCerrados = $this->validarTodosLosCortesCerrados($anioEscolar);
        $promediosGenerados = $this->validarPromediosFinalesGenerados($anioEscolar);
        $estadosDefinidos = $this->validarEstadosFinalesEstudiantes($anioEscolar);

        $asignacionesPendientes = $anioEscolar->getAsignacionesConCortesPendientes();

        return [
            'puede_cerrar' => $cortesCerrados && $promediosGenerados && $estadosDefinidos,
            'validaciones' => [
                'cortes_cerrados' => [
                    'cumple' => $cortesCerrados,
                    'mensaje' => $cortesCerrados 
                        ? 'Todos los cortes evaluativos están cerrados por todos los docentes.'
                        : 'Faltan cortes por cerrar: ' . $asignacionesPendientes->count() . ' asignaciones pendientes.',
                    'detalle' => $asignacionesPendientes->map(fn($a) => [
                        'asignatura' => $a->asignatura?->nombre ?? 'Sin asignatura',
                        'docente' => $a->docente?->usuario?->name ?? ($a->docente?->nombre_completo ?? 'Sin docente'),
                        'aula' => $a->aula?->nombre ?? 'Sin aula',
                    ])->values()->toArray(),
                ],
                'promedios_generados' => [
                    'cumple' => $promediosGenerados,
                    'mensaje' => $promediosGenerados
                        ? 'Todos los promedios finales están generados.'
                        : 'Hay estudiantes sin nota final en alguna asignatura (IV Corte).',
                ],
                'estados_estudiantes_definidos' => [
                    'cumple' => $estadosDefinidos,
                    'mensaje' => $estadosDefinidos
                        ? 'Todos los estudiantes tienen estado final definido.'
                        : 'Hay estudiantes sin estado final (falta nota o examen de reparación).',
                ],
            ],
            'resumen_estadistico' => $anioEscolar->getResumenEstadistico(),
        ];
    }

    /**
     * Crea un nuevo año escolar en estado Planificación.
     */
    public function crear(array $data, ?Usuario $usuario = null): AnioEscolar
    {
        return DB::transaction(function () use ($data, $usuario) {
            $anioEscolar = AnioEscolar::create([
                'nombre' => $data['nombre'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'estado' => AnioEscolar::ESTADO_PLANIFICACION,
            ]);

            $this->registrarAuditoria(
                $anioEscolar,
                'creado',
                $usuario,
                "Año escolar creado en estado Planificación."
            );

            return $anioEscolar;
        });
    }

    /**
     * Actualiza datos básicos de un año escolar (solo si no está cerrado).
     */
    public function actualizar(AnioEscolar $anioEscolar, array $data, ?Usuario $usuario = null): AnioEscolar
    {
        if ($anioEscolar->es_cerrado) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede modificar un año escolar cerrado.',
            ]);
        }

        $anioEscolar->fill($data);
        $anioEscolar->save();

        $this->registrarAuditoria(
            $anioEscolar,
            'actualizado',
            $usuario,
            "Datos del año escolar actualizados."
        );

        return $anioEscolar->fresh();
    }

    /**
     * Elimina un año escolar (solo si está en planificación y no tiene dependencias).
     */
    public function eliminar(AnioEscolar $anioEscolar, ?Usuario $usuario = null): bool
    {
        if (!$anioEscolar->es_planificacion) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden eliminar años escolares en estado Planificación.',
            ]);
        }

        // Verificar que no tenga dependencias
        $tieneDependencias = $anioEscolar->aulas()->exists() 
            || $anioEscolar->matriculas()->exists() 
            || $anioEscolar->asignaciones()->exists()
            || $anioEscolar->cortesEvaluativos()->exists();

        if ($tieneDependencias) {
            throw ValidationException::withMessages([
                'dependencias' => 'No se puede eliminar: el año escolar tiene aulas, matrículas, asignaciones o cortes evaluativos asociados.',
            ]);
        }

        $this->registrarAuditoria(
            $anioEscolar,
            'eliminado',
            $usuario,
            "Año escolar eliminado (estaba en Planificación sin dependencias)."
        );

        return $anioEscolar->delete();
    }

    /**
     * Obtiene el año escolar activo o crea uno por defecto si no existe (para desarrollo).
     */
    public function obtenerOcrearActivo(?Usuario $usuario = null): AnioEscolar
    {
        $activo = AnioEscolar::obtenerActivo();
        
        if ($activo) {
            return $activo;
        }

        // Si no hay activo, buscar uno en planificación para activar
        $enPlanificacion = AnioEscolar::planificacion()->first();
        
        if ($enPlanificacion) {
            return $this->activar($enPlanificacion, $usuario);
        }

        // Como último recurso, crear uno por defecto (solo desarrollo)
        if (app()->environment('local', 'testing')) {
            $anio = now()->year;
            return $this->crear([
                'nombre' => "{$anio}-" . ($anio + 1),
                'fecha_inicio' => "{$anio}-02-01",
                'fecha_fin' => ($anio + 1) . "-11-30",
            ], $usuario);
        }

        throw new \RuntimeException('No hay año escolar activo ni en planificación para activar.');
    }

    /**
     * Registra auditoría de cambios en el año escolar.
     * En producción, esto debería ir a una tabla de auditoría dedicada.
     */
    protected function registrarAuditoria(
        AnioEscolar $anioEscolar,
        string $accion,
        ?Usuario $usuario,
        string $descripcion,
        array $metadata = []
    ): void {
        \Log::channel('auditoria')->info("AnioEscolar: {$accion}", array_merge([
            'anio_escolar_id' => $anioEscolar->id,
            'anio_escolar_nombre' => $anioEscolar->nombre,
            'accion' => $accion,
            'usuario_id' => $usuario?->id,
            'usuario_nombre' => $usuario?->name,
            'descripcion' => $descripcion,
            'fecha' => now()->toISOString(),
        ], $metadata));
    }
}