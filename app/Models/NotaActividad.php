<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaActividad extends Model
{
    protected $table = 'nota_actividad';

    protected $fillable = [
        'matricula_id',
        'actividad_evaluativa_id',
        'nota_obtenida',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function actividadEvaluativa()
    {
        return $this->belongsTo(ActividadEvaluativa::class, 'actividad_evaluativa_id');
    }
}