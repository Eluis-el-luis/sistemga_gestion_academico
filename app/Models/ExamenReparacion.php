<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenReparacion extends Model
{
    protected $table = 'examen_reparacion';

    protected $fillable = [
        'matricula_id',
        'asignatura_id',
        'nota_obtenida',
        'fecha',
        'resultado',
    ];

    protected $casts = [
        'nota_obtenida' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class, 'asignatura_id');
    }
}