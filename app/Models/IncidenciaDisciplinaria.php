<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaDisciplinaria extends Model
{
    protected $table = 'incidencia_disciplinaria';

    protected $fillable = [
        'matricula_id',
        'docente_reporta_id',
        'coordinador_atiende_id',
        'nivel_falta',
        'descripcion',
        'estado',
        'fecha_incidencia',
        'fecha_citacion_padres',
        'resolucion_final',
    ];

    protected $casts = [
        'fecha_incidencia' => 'date',
        'fecha_citacion_padres' => 'datetime',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function docenteReporta(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_reporta_id');
    }

    public function coordinadorAtiende(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'coordinador_atiende_id');
    }
}