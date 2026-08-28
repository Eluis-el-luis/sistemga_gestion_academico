<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadEvaluativa extends Model
{
    use HasFactory;

    protected $table = 'actividad_evaluativa'; 

    protected $fillable = [
        'aula_asignatura_docente_id',
        'corte_evaluativo_id',
        'nombre',
        'tipo', // ej: 'Acumulado', 'Examen'
        'puntaje_maximo'
    ];

    public function asignaturaAula()
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }

    public function corteEvaluativo()
    {
        return $this->belongsTo(CorteEvaluativo::class, 'corte_evaluativo_id');
    }

    public function calificaciones()
    {
        return $this->hasMany(CalificacionActividad::class, 'actividad_evaluativa_id');
    }
}