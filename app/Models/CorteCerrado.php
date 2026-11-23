<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorteCerrado extends Model
{
    protected $table = 'corte_cerrado';

    protected $fillable = [
        'aula_asignatura_docente_id',
        'corte_evaluativo_id',
        'bloqueado',
        'cerrado_por',
        'fecha_cierre',
    ];

    protected $casts = [
        'bloqueado' => 'boolean',
        'fecha_cierre' => 'datetime',
    ];

    public function aulaAsignaturaDocente(): BelongsTo
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }

    public function corteEvaluativo(): BelongsTo
    {
        return $this->belongsTo(CorteEvaluativo::class, 'corte_evaluativo_id');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cerrado_por');
    }
}