<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvanceContenido extends Model
{
    protected $table = 'avance_contenido';
    protected $fillable = ['aula_asignatura_docente_id', 'mes', 'porcentaje_avance'];

    public function aulaAsignaturaDocente()
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }
}
