<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;
    protected $table = 'docente';
    protected $fillable = ['usuario_id', 'codigo_unico_persona', 'sexo', 'es_coordinador', 'modalidad_coordina_id'];
}