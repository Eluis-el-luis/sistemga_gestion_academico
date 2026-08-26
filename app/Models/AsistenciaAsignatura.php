<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'observacion'
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class);
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    public function bloqueHorario()
    {
        return $this->belongsTo(BloqueHorario::class);
    }
}