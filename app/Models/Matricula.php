<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matricula extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'matricula';
    protected $fillable = ['alumno_id', 'aula_id', 'anio_escolar_id', 'estado', 'fecha_matricula', 'fecha_retiro'];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }
    
    public function boletines()
    {
        return $this->hasMany(Boletin::class, 'matricula_id');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'matricula_id');
    }
}