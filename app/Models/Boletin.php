<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boletin extends Model
{
    protected $table = 'boletin';
    protected $fillable = ['matricula_id', 'corte_evaluativo_id', 'fecha_generacion', 'archivo_path'];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function corteEvaluativo()
    {
        return $this->belongsTo(CorteEvaluativo::class, 'corte_evaluativo_id');
    }
}
