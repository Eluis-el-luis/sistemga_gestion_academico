<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionFamiliar extends Model
{
    protected $table = 'evaluacion_familiar';
    protected $fillable = ['matricula_id', 'corte_evaluativo_id', 'evaluacion'];
}
