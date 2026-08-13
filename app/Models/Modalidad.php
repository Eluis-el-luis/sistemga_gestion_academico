<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    use HasFactory;
    // Indicamos el nombre exacto de la tabla, ya que por convención
    // Laravel buscaría "modalidads" en lugar de "modalidades"
    protected $table = 'modalidad';

    // Habilitamos el campo para que pueda recibir datos
    protected $fillable = ['nombre'];
}