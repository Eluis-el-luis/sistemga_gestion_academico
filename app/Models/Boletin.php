<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boletin extends Model
{
    protected $table = 'boletin';

    protected $fillable = [
        'matricula_id',
        'corte_evaluativo_id',
        'fecha_generacion',
        'archivo_path',
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
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