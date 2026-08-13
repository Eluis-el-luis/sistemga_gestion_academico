<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;
    protected $table = 'horario';
    protected $fillable = ['aula_asignatura_docente_id', 'dia_semana', 'hora_inicio', 'hora_fin'];
}