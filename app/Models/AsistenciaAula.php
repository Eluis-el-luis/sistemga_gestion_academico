<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaAula extends Model
{
    protected $table = 'asistencia_aula';
    protected $fillable = ['matricula_id', 'fecha', 'presente', 'justificada'];
}
