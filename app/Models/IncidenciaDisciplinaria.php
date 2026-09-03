<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'resolucion_final'
    ];

    protected $casts = [
        'fecha_incidencia' => 'date',
        'fecha_citacion_padres' => 'datetime',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function docenteReporta()
    {
        return $this->belongsTo(Docente::class, 'docente_reporta_id');
    }

    public function coordinadorAtiende()
    {
        return $this->belongsTo(Usuario::class, 'coordinador_atiende_id');
    }
}
