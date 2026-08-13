<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaDocente extends Model
{
    protected $table = 'asistencia_docente';
    protected $fillable = ['docente_id', 'aula_asignatura_docente_id', 'fecha', 'presente'];
}
