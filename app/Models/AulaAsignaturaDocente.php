<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AulaAsignaturaDocente extends Model
{
    use HasFactory;
    protected $table = 'aula_asignatura_docente';
    protected $fillable = ['aula_id', 'asignatura_id', 'docente_id', 'anio_escolar_id', 'horas_semanales'];

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'asignatura_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'aula_asignatura_docente_id');
    }
}