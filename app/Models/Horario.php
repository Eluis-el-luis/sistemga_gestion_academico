<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BloqueHorario;

class Horario extends Model
{
    use HasFactory;
    
    protected $table = 'horario';
    
    // Cambiamos hora_inicio y hora_fin por bloque_horario_id
    protected $fillable = ['aula_asignatura_docente_id', 'dia_semana', 'bloque_horario_id'];

    public function aulaAsignaturaDocente()
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }

    // Nueva relación al bloque maestro
    public function bloque()
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque_horario_id');
    }

    public function bloqueHorario()
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque_horario_id');
    }
}