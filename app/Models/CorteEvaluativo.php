<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteEvaluativo extends Model
{
    use HasFactory;
    
    protected $table = 'corte_evaluativo';
    protected $fillable = ['anio_escolar_id', 'numero', 'semestre', 'fecha_inicio', 'fecha_fin', 'peso_acumulado', 'peso_examen'];

   
    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }
}