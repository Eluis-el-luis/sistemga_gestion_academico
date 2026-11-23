<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nota extends Model
{
    protected $table = 'nota';

    protected $fillable = [
        'matricula_id',
        'aula_asignatura_docente_id',
        'corte_evaluativo_id',
        'nota_cuantitativa',
        'porcentaje_avance',
        'indicador_logro_id',
        'updated_by',
    ];

    protected $casts = [
        'nota_cuantitativa' => 'decimal:2',
        'porcentaje_avance' => 'decimal:2',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function aulaAsignaturaDocente(): BelongsTo
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }

    public function indicadorLogro(): BelongsTo
    {
        return $this->belongsTo(IndicadorLogro::class, 'indicador_logro_id');
    }

    public function corteEvaluativo(): BelongsTo
    {
        return $this->belongsTo(CorteEvaluativo::class, 'corte_evaluativo_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'updated_by');
    }
}