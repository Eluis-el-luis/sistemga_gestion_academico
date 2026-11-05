<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Modelo para la gestión del Año Escolar.
 * 
 * Estados válidos:
 * - planificacion: Año futuro en configuración (secciones, carga horaria, matrículas)
 * - activo: Año actual en operaciones (asistencias, calificaciones, operaciones diarias)
 * - cerrado: Año finalizado (solo lectura, inmutabilidad del historial académico)
 * 
 * Reglas de negocio:
 * - Solo puede haber UN año escolar en estado "activo" a la vez
 * - El cambio de "activo" a "cerrado" requiere validación estricta:
 *   1. Todos los docentes han subido y cerrado sus actas de calificaciones
 *   2. Los promedios finales definitivos están generados
 *   3. El estado final de los estudiantes (Aprobado/Reprobado/Reparación) está definido
 */
class AnioEscolar extends Model
{
    use HasFactory;

    protected $table = 'anio_escolar';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'fecha_cierre',
        'cerrado_por',
        'observaciones_cierre',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_cierre' => 'datetime',
        'estado' => 'string',
    ];

    /**
     * Estados válidos del año escolar
     */
    public const ESTADO_PLANIFICACION = 'planificacion';
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_CERRADO = 'cerrado';

    public const ESTADOS_VALIDOS = [
        self::ESTADO_PLANIFICACION,
        self::ESTADO_ACTIVO,
        self::ESTADO_CERRADO,
    ];

    /**
     * Etiquetas legibles para los estados
     */
    public const ETIQUETAS_ESTADOS = [
        self::ESTADO_PLANIFICACION => 'Planificación',
        self::ESTADO_ACTIVO => 'Activo',
        self::ESTADO_CERRADO => 'Cerrado',
    ];

    /**
     * Colores para badges/UI
     */
    public const COLORES_ESTADOS = [
        self::ESTADO_PLANIFICACION => 'bg-blue-100 text-blue-800',
        self::ESTADO_ACTIVO => 'bg-green-100 text-green-800',
        self::ESTADO_CERRADO => 'bg-gray-100 text-gray-800',
    ];

    /**
     * Iconos para UI
     */
    public const ICONOS_ESTADOS = [
        self::ESTADO_PLANIFICACION => 'heroicon-o-calendar-days',
        self::ESTADO_ACTIVO => 'heroicon-o-check-circle',
        self::ESTADO_CERRADO => 'heroicon-o-lock-closed',
    ];

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Año escolar en planificación
     */
    public function scopePlanificacion(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PLANIFICACION);
    }

    /**
     * Scope: Año escolar activo (solo puede haber uno)
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    /**
     * Scope: Año escolar cerrado
     */
    public function scopeCerrado(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_CERRADO);
    }

    /**
     * Scope: Años escolares no cerrados (planificación + activo)
     */
    public function scopeNoCerrados(Builder $query): Builder
    {
        return $query->whereIn('estado', [self::ESTADO_PLANIFICACION, self::ESTADO_ACTIVO]);
    }

    /**
     * Scope: Ordenar por fecha de inicio descendente (más reciente primero)
     */
    public function scopeOrdenado(Builder $query): Builder
    {
        return $query->orderByDesc('fecha_inicio');
    }

    // ========================================
    // ACCESORES Y MUTADORES
    // ========================================

    /**
     * Obtener la etiqueta legible del estado
     */
    public function getEtiquetaEstadoAttribute(): string
    {
        return self::ETIQUETAS_ESTADOS[$this->estado] ?? $this->estado;
    }

    /**
     * Obtener la clase CSS para el badge del estado
     */
    public function getColorEstadoAttribute(): string
    {
        return self::COLORES_ESTADOS[$this->estado] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Obtener el icono para el estado
     */
    public function getIconoEstadoAttribute(): string
    {
        return self::ICONOS_ESTADOS[$this->estado] ?? 'heroicon-o-question-mark-circle';
    }

    /**
     * Verifica si el año está en planificación
     */
    public function getEsPlanificacionAttribute(): bool
    {
        return $this->estado === self::ESTADO_PLANIFICACION;
    }

    /**
     * Verifica si el año está activo
     */
    public function getEsActivoAttribute(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    /**
     * Verifica si el año está cerrado
     */
    public function getEsCerradoAttribute(): bool
    {
        return $this->estado === self::ESTADO_CERRADO;
    }

    /**
     * Verifica si el año puede ser modificado (no cerrado)
     */
    public function getEsModificableAttribute(): bool
    {
        return $this->estado !== self::ESTADO_CERRADO;
    }

    /**
     * Verifica si el año puede recibir operaciones académicas (activo)
     */
    public function getEsOperativoAttribute(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    /**
     * Mutador para el estado - valida transiciones permitidas
     */
    public function setEstadoAttribute(string $value): void
    {
        $value = strtolower(trim($value));
        
        if (!in_array($value, self::ESTADOS_VALIDOS)) {
            throw new \InvalidArgumentException(
                "Estado inválido: {$value}. Estados permitidos: " . implode(', ', self::ESTADOS_VALIDOS)
            );
        }

        $this->attributes['estado'] = $value;
    }

    /**
     * Obtener el nombre del período escolar formateado
     */
    public function getNombrePeriodoAttribute(): string
    {
        return $this->fecha_inicio->format('Y') . '-' . $this->fecha_fin->format('Y');
    }

    /**
     * Verifica si el año escolar está vigente (fecha actual dentro del rango)
     */
    public function getEstaVigenteAttribute(): bool
    {
        $hoy = Carbon::today();
        return $hoy->between($this->fecha_inicio, $this->fecha_fin);
    }

    // ========================================
    // RELACIONES
    // ========================================

    /**
     * Cortes evaluativos del año escolar
     */
    public function cortesEvaluativos(): HasMany
    {
        return $this->hasMany(CorteEvaluativo::class, 'anio_escolar_id')->orderBy('numero');
    }

    /**
     * Aulas del año escolar
     */
    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'anio_escolar_id');
    }

    /**
     * Matrículas del año escolar
     */
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'anio_escolar_id');
    }

    /**
     * Asignaciones docente-aula-asignatura del año escolar
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(AulaAsignaturaDocente::class, 'anio_escolar_id');
    }

    /**
     * Usuario que cerró el año escolar
     */
    public function cerradoPor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cerrado_por');
    }

    // ========================================
    // MÉTODOS DE NEGOCIO
    // ========================================

    /**
     * Obtiene el año escolar activo actual (singleton)
     */
    public static function obtenerActivo(): ?self
    {
        return self::activo()->first();
    }

    /**
     * Obtiene el año escolar activo o lanza excepción si no existe
     */
    public static function obtenerActivoOFail(): self
    {
        return self::activo()->firstOrFail();
    }

    /**
     * Verifica si ya existe un año escolar activo
     */
    public static function existeActivo(): bool
    {
        return self::activo()->exists();
    }

    /**
     * Verifica si el año puede cambiar a Planificación
     * Solo si no hay ningún año activo y este no está cerrado
     */
    public function puedeCambiarAPlanificacion(): bool
    {
        if ($this->es_cerrado) {
            return false;
        }
        // Si ya es planificación, no hay cambio
        if ($this->es_planificacion) {
            return true;
        }
        // Si es activo, no puede volver a planificación directamente
        if ($this->es_activo) {
            return false;
        }
        return true;
    }

    /**
     * Verifica si el año puede cambiar a Activo
     * Requiere que no haya otro año activo
     */
    public function puedeCambiarAActivo(): bool
    {
        if ($this->es_cerrado) {
            return false;
        }
        if ($this->es_activo) {
            return true; // Ya es activo
        }
        // No puede haber otro año activo
        return !self::existeActivo();
    }

    /**
     * Verifica si el año puede cambiar a Cerrado
     * Requiere que esté activo y pase todas las validaciones
     */
    public function puedeCambiarACerrado(): bool
    {
        return $this->es_activo;
    }

    /**
     * Obtiene las transiciones de estado permitidas desde el estado actual
     */
    public function getTransicionesPermitidas(): array
    {
        $transiciones = [];

        if ($this->es_planificacion) {
            if ($this->puedeCambiarAActivo()) {
                $transiciones[] = self::ESTADO_ACTIVO;
            }
        } elseif ($this->es_activo) {
            if ($this->puedeCambiarACerrado()) {
                $transiciones[] = self::ESTADO_CERRADO;
            }
        }
        // Cerrado no tiene transiciones (inmutable)

        return $transiciones;
    }

    /**
     * Obtiene resumen estadístico del año escolar
     */
    public function getResumenEstadistico(): array
    {
        return [
            'total_aulas' => $this->aulas()->count(),
            'total_matriculas_activas' => $this->matriculas()->where('estado', 'activo')->count(),
            'total_docentes_asignados' => $this->asignaciones()->where('activo', true)->count(),
            'total_cortes' => $this->cortesEvaluativos()->count(),
            'cortes_cerrados' => $this->getCortesCerradosCount(),
            'porcentaje_cortes_cerrados' => $this->getPorcentajeCortesCerrados(),
        ];
    }

    /**
     * Cuenta cuántos cortes están completamente cerrados (todas las asignaciones)
     */
    public function getCortesCerradosCount(): int
    {
        $totalAsignacionesActivas = $this->asignaciones()->where('activo', true)->count();
        
        if ($totalAsignacionesActivas === 0) {
            return 0;
        }

        $cortesCerrados = 0;
        
        foreach ($this->cortesEvaluativos as $corte) {
            $cerradosEnCorte = CorteCerrado::where('corte_evaluativo_id', $corte->id)
                ->whereHas('aulaAsignaturaDocente', function ($q) {
                    $q->where('activo', true);
                })
                ->where('bloqueado', true)
                ->count();

            if ($cerradosEnCorte >= $totalAsignacionesActivas) {
                $cortesCerrados++;
            }
        }

        return $cortesCerrados;
    }

    /**
     * Porcentaje de cortes cerrados
     */
    public function getPorcentajeCortesCerrados(): float
    {
        $totalCortes = $this->cortesEvaluativos()->count();
        
        if ($totalCortes === 0) {
            return 0.0;
        }

        return round(($this->getCortesCerradosCount() / $totalCortes) * 100, 2);
    }

    /**
     * Verifica si todos los cortes están cerrados
     */
    public function getTodosLosCortesCerrados(): bool
    {
        $totalCortes = $this->cortesEvaluativos()->count();
        
        if ($totalCortes === 0) {
            return false; // Sin cortes no se puede cerrar
        }

        return $this->getCortesCerradosCount() === $totalCortes;
    }

    /**
     * Obtiene las asignaciones que NO tienen cerrados todos los cortes
     */
    public function getAsignacionesConCortesPendientes(): \Illuminate\Database\Eloquent\Collection
    {
        $asignaciones = $this->asignaciones()
            ->where('activo', true)
            ->with(['cortesCerrados.corteEvaluativo', 'docente.usuario', 'asignatura', 'aula'])
            ->get();

        return $asignaciones->filter(function ($asignacion) {
            $cortesCerrados = $asignacion->cortesCerrados
                ->where('bloqueado', true)
                ->count();
            
            $totalCortes = $this->cortesEvaluativos()->count();
            
            return $cortesCerrados < $totalCortes;
        })->values();
    }

    /**
     * Verifica si todos los estudiantes tienen estado final definido
     * (Aprobado/Reprobado/Reparación) - basado en notas finales y exámenes de reparación
     */
    public function getTodosEstudiantesConEstadoFinal(): bool
    {
        $matriculasActivas = $this->matriculas()
            ->where('estado', 'activo')
            ->with(['alumno', 'aula', 'notas'])
            ->get();

        if ($matriculasActivas->isEmpty()) {
            return false;
        }

        foreach ($matriculasActivas as $matricula) {
            // Verificar que el estudiante tenga notas finales en todas sus asignaturas
            $asignacionesDelAula = $matricula->aula->asignaciones()
                ->where('activo', true)
                ->get();

            foreach ($asignacionesDelAula as $asignacion) {
                $notaFinal = $matricula->notas()
                    ->where('aula_asignatura_docente_id', $asignacion->id)
                    ->whereHas('corteEvaluativo', fn($q) => $q->where('numero', 4)) // IV corte
                    ->first();

                if (!$notaFinal || is_null($notaFinal->nota_cuantitativa)) {
                    return false; // Falta nota final en alguna asignatura
                }

                // Si la nota es < 60, debe tener examen de reparación definido
                if ($notaFinal->nota_cuantitativa < 60) {
                    $tieneReparacion = \App\Models\ExamenReparacion::where('matricula_id', $matricula->id)
                        ->where('asignatura_id', $asignacion->asignatura_id)
                        ->exists();

                    if (!$tieneReparacion) {
                        return false; // Falta definir reparación
                    }
                }
            }
        }

        return true;
    }
}