<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AulaAsignaturaDocente extends Model
{
    use HasFactory;
    protected $table = 'aula_asignatura_docente';
    protected $fillable = ['aula_id', 'asignatura_id', 'docente_id', 'anio_escolar_id', 'horas_semanales'];
}