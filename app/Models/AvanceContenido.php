<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvanceContenido extends Model
{
    protected $table = 'avance_contenido';

    protected $fillable = [
        'aula_asignatura_docente_id',
        'mes',
        'porcentaje_avance',
    ];

    protected $casts = [
        'mes' => 'integer',
        'porcentaje_avance' => 'decimal:2',
    ];

    public function aulaAsignaturaDocente(): BelongsTo
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }
}