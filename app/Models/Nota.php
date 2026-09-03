<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = 'nota';
    protected $fillable = ['matricula_id', 'aula_asignatura_docente_id', 'corte_evaluativo_id', 'nota_cuantitativa', 'indicador_logro_id'];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function aulaAsignaturaDocente()
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }

    public function indicadorLogro()
    {
        return $this->belongsTo(IndicadorLogro::class, 'indicador_logro_id');
    }

    public function corteEvaluativo()
    {
        return $this->belongsTo(CorteEvaluativo::class, 'corte_evaluativo_id');
    }
}
