<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaAsignatura extends Model
{
    protected $table = 'asistencia_asignatura';
    protected $fillable = ['matricula_id', 'aula_asignatura_docente_id', 'fecha', 'presente'];
}
