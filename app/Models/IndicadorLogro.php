<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicadorLogro extends Model
{
    use HasFactory;
    protected $table = 'indicador_logro';
    protected $fillable = ['codigo', 'nombre', 'nota_min', 'nota_max', 'modalidad_id', 'grado_min', 'grado_max'];
}