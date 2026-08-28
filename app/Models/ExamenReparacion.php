<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamenReparacion extends Model
{
    protected $table = 'examen_reparacion';
    protected $fillable = ['matricula_id', 'asignatura_id', 'nota_obtenida', 'fecha', 'resultado'];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'asignatura_id');
    }
}
