<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaAsignatura extends Model
{
    use HasFactory;

    protected $table = 'asistencia_asignatura';

    protected $fillable = [
        'matricula_id',
        'asignatura_id',
        'bloque_horario_id',
        'fecha',
        'estado_incidencia',
        'observacion',
    ];

    protected $casts = [
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

    public function bloqueHorario(): BelongsTo
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque_horario_id');
    }
}