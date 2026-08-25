<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalificacionActividad extends Model
{
    use HasFactory;

    protected $table = 'calificacion_actividad'; 

    protected $fillable = [
        'actividad_evaluativa_id',
        'matricula_id',
        'puntaje_obtenido'
    ];

    public function actividad()
    {
        return $this->belongsTo(ActividadEvaluativa::class, 'actividad_evaluativa_id');
    }

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
}