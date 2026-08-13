<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;
    protected $table = 'aula';
    protected $fillable = ['nombre', 'grado_id', 'modalidad_id', 'turno', 'docente_guia_id', 'anio_escolar_id', 'cupo'];
}