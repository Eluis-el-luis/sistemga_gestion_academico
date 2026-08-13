<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    use HasFactory;
    protected $table = 'matricula';
    protected $fillable = ['alumno_id', 'aula_id', 'anio_escolar_id', 'estado', 'fecha_matricula', 'fecha_retiro'];
}