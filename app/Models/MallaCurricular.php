<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MallaCurricular extends Model
{
    use HasFactory;
    protected $table = 'malla_curricular';
    protected $fillable = ['grado_id', 'asignatura_id', 'horas_semanales_sugeridas', 'activo'];
}