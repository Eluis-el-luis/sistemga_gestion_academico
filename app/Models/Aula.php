<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;
    protected $table = 'aula';
    protected $fillable = ['nombre', 'grado_id', 'modalidad_id', 'turno', 'docente_guia_id', 'anio_escolar_id', 'cupo'];

    public function docenteGuia()
    {
        // Especificamos 'docente_guia_id' porque no sigue la convención clásica 'docente_id'
        return $this->belongsTo(Docente::class, 'docente_guia_id');
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'aula_id');
    }

}