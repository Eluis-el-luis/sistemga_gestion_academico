<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluacionFamiliar extends Model
{
    protected $table = 'evaluacion_familiar';

    protected $fillable = [
        'matricula_id',
        'corte_evaluativo_id',
        'evaluacion',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function corteEvaluativo(): BelongsTo
    {
        return $this->belongsTo(CorteEvaluativo::class, 'corte_evaluativo_id');
    }
}