<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;
    protected $table = 'alumno';
    protected $fillable = ['usuario_id', 'codigo_unico_persona', 'nombre_completo', 'sexo', 'fecha_nacimiento'];
}