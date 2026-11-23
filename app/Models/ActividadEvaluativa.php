<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActividadEvaluativa extends Model
{
    use HasFactory;

    protected $table = 'actividad_evaluativa';

    protected $fillable = [
        'aula_asignatura_docente_id',
        'corte_evaluativo_id',
        'unidad_numero',
        'porcentaje_peso',
        'nombre',
        'descripcion',
        'fecha',
        'tipo',
        'puntaje_maximo',
    ];

    protected $casts = [
        'unidad_numero' => 'integer',
        'porcentaje_peso' => 'decimal:2',
        'fecha' => 'date',
        'puntaje_maximo' => 'decimal:2',
    ];

    public function asignaturaAula(): BelongsTo
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }

    public function corteEvaluativo(): BelongsTo
    {
        return $this->belongsTo(CorteEvaluativo::class, 'corte_evaluativo_id');
    }

    public function notasActividad(): HasMany
    {
        return $this->hasMany(NotaActividad::class, 'actividad_evaluativa_id');
    }
}