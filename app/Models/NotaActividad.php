<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaActividad extends Model
{
    protected $table = 'nota_actividad';

    protected $fillable = [
        'matricula_id',
        'actividad_evaluativa_id',
        'nota_obtenida',
        'indicador_logro_id',
        'updated_by',
    ];

    protected $casts = [
        'nota_obtenida' => 'decimal:2',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function actividadEvaluativa(): BelongsTo
    {
        return $this->belongsTo(ActividadEvaluativa::class, 'actividad_evaluativa_id');
    }

    public function indicadorLogro(): BelongsTo
    {
        return $this->belongsTo(IndicadorLogro::class, 'indicador_logro_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'updated_by');
    }
}