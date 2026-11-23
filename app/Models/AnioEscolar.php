<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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

    public const ESTADO_PLANIFICACION = 'planificacion';
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_CERRADO = 'cerrado';

    public const ESTADOS_VALIDOS = [
        self::ESTADO_PLANIFICACION,
        self::ESTADO_ACTIVO,
        self::ESTADO_CERRADO,
    ];

    public const ETIQUETAS_ESTADOS = [
        self::ESTADO_PLANIFICACION => 'Planificación',
        self::ESTADO_ACTIVO => 'Activo',
        self::ESTADO_CERRADO => 'Cerrado',
    ];

    public const COLORES_ESTADOS = [
        self::ESTADO_PLANIFICACION => 'bg-blue-100 text-blue-800',
        self::ESTADO_ACTIVO => 'bg-green-100 text-green-800',
        self::ESTADO_CERRADO => 'bg-gray-100 text-gray-800',
    ];

    public const ICONOS_ESTADOS = [
        self::ESTADO_PLANIFICACION => 'heroicon-o-calendar-days',
        self::ESTADO_ACTIVO => 'heroicon-o-check-circle',
        self::ESTADO_CERRADO => 'heroicon-o-lock-closed',
    ];

    public function scopePlanificacion(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PLANIFICACION);
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    public function scopeCerrado(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_CERRADO);
    }

    public function scopeNoCerrados(Builder $query): Builder
    {
        return $query->whereIn('estado', [self::ESTADO_PLANIFICACION, self::ESTADO_ACTIVO]);
    }

    public function scopeOrdenado(Builder $query): Builder
    {
        return $query->orderByDesc('fecha_inicio');
    }

    public function getEtiquetaEstadoAttribute(): string
    {
        return self::ETIQUETAS_ESTADOS[$this->estado] ?? $this->estado;
    }

    public function getColorEstadoAttribute(): string
    {
        return self::COLORES_ESTADOS[$this->estado] ?? 'bg-gray-100 text-gray-800';
    }

    public function getIconoEstadoAttribute(): string
    {
        return self::ICONOS_ESTADOS[$this->estado] ?? 'heroicon-o-question-mark-circle';
    }

    public function getEsPlanificacionAttribute(): bool
    {
        return $this->estado === self::ESTADO_PLANIFICACION;
    }

    public function getEsActivoAttribute(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    public function getEsCerradoAttribute(): bool
    {
        return $this->estado === self::ESTADO_CERRADO;
    }

    public function getEsModificableAttribute(): bool
    {
        return $this->estado !== self::ESTADO_CERRADO;
    }

    public function getEsOperativoAttribute(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    public function setEstadoAttribute(string $value): void
    {
        $value = strtolower(trim($value));

        if (!in_array($value, self::ESTADOS_VALIDOS, true)) {
            throw new \InvalidArgumentException(
                "Estado inválido: {$value}. Estados permitidos: " . implode(', ', self::ESTADOS_VALIDOS)
            );
        }

        $this->attributes['estado'] = $value;
    }

    public function getNombrePeriodoAttribute(): string
    {
        return $this->fecha_inicio->format('Y') . '-' . $this->fecha_fin->format('Y');
    }

    public function getEstaVigenteAttribute(): bool
    {
        $hoy = Carbon::today();
        return $hoy->between($this->fecha_inicio, $this->fecha_fin);
    }

    public function cortesEvaluativos(): HasMany
    {
        return $this->hasMany(CorteEvaluativo::class, 'anio_escolar_id')->orderBy('numero');
    }

    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'anio_escolar_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'anio_escolar_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AulaAsignaturaDocente::class, 'anio_escolar_id');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cerrado_por');
    }

    public static function obtenerActivo(): ?self
    {
        return self::activo()->first();
    }

    public static function obtenerActivoOFail(): self
    {
        return self::activo()->firstOrFail();
    }

    public static function existeActivo(): bool
    {
        return self::activo()->exists();
    }

    public function puedeCambiarAPlanificacion(): bool
    {
        if ($this->es_cerrado) {
            return false;
        }

        if ($this->es_planificacion) {
            return true;
        }

        return !$this->es_activo;
    }

    public function puedeCambiarAActivo(): bool
    {
        if ($this->es_cerrado) {
            return false;
        }

        if ($this->es_activo) {
            return true;
        }

        return !self::existeActivo();
    }

    public function puedeCambiarACerrado(): bool
    {
        return $this->es_activo;
    }

    public function getTransicionesPermitidas(): array
    {
        $transiciones = [];

        if ($this->es_planificacion && $this->puedeCambiarAActivo()) {
            $transiciones[] = self::ESTADO_ACTIVO;
        } elseif ($this->es_activo && $this->puedeCambiarACerrado()) {
            $transiciones[] = self::ESTADO_CERRADO;
        }

        return $transiciones;
    }

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

    public function getPorcentajeCortesCerrados(): float
    {
        $totalCortes = $this->cortesEvaluativos()->count();

        if ($totalCortes === 0) {
            return 0.0;
        }

        return round(($this->getCortesCerradosCount() / $totalCortes) * 100, 2);
    }

    public function getTodosLosCortesCerrados(): bool
    {
        $totalCortes = $this->cortesEvaluativos()->count();

        if ($totalCortes === 0) {
            return false;
        }

        return $this->getCortesCerradosCount() === $totalCortes;
    }

    public function getAsignacionesConCortesPendientes(): \Illuminate\Database\Eloquent\Collection
    {
        $asignaciones = $this->asignaciones()
            ->where('activo', true)
            ->with(['cortesCerrados.corteEvaluativo', 'docente.usuario', 'asignatura', 'aula'])
            ->get();

        $totalCortes = $this->cortesEvaluativos()->count();

        return $asignaciones->filter(function ($asignacion) use ($totalCortes) {
            $cortesCerrados = $asignacion->cortesCerrados
                ->where('bloqueado', true)
                ->count();

            return $cortesCerrados < $totalCortes;
        })->values();
    }

    public function getTodosEstudiantesConEstadoFinal(): bool
    {
        $matriculasActivas = $this->matriculas()
            ->where('estado', 'activo')
            ->with(['alumno', 'aula.modalidad', 'aula.asignaciones', 'notas.corteEvaluativo'])
            ->get();

        if ($matriculasActivas->isEmpty()) {
            return false;
        }

        foreach ($matriculasActivas as $matricula) {
            $esPreescolar = str_contains(
                strtolower((string) optional($matricula->aula->modalidad)->nombre),
                'preescolar'
            );

            $asignacionesDelAula = $matricula->aula->asignaciones
                ->where('activo', true);

            foreach ($asignacionesDelAula as $asignacion) {
                $notaFinal = $matricula->notas
                    ->where('aula_asignatura_docente_id', $asignacion->id)
                    ->first(fn ($nota) => optional($nota->corteEvaluativo)->numero === 4);

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

                if ($notaFinal->nota_cuantitativa < 60) {
                    $tieneReparacion = ExamenReparacion::where('matricula_id', $matricula->id)
                        ->where('asignatura_id', $asignacion->asignatura_id)
                        ->exists();

                    if (!$tieneReparacion) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}