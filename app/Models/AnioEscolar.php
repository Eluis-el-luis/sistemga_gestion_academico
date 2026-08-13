<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnioEscolar extends Model
{
    use HasFactory;

    // Indicamos el nombre exacto de la tabla, ya que por convención 
    // Laravel buscaría "anio_escolars" en lugar de "anio_escolares"
    protected $table = 'anio_escolar';

    // Habilitamos los campos permitidos
    protected $fillable = [
        'nombre', 
        'fecha_inicio', 
        'fecha_fin', 
        'activo'
    ];
}