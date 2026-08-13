<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boletin extends Model
{
    protected $table = 'boletin';
    protected $fillable = ['matricula_id', 'corte_evaluativo_id', 'fecha_generacion', 'archivo_path'];
}
