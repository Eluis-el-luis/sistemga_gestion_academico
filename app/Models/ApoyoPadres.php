<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApoyoPadres extends Model
{
    protected $table = 'apoyo_padres';
    protected $fillable = ['aula_id', 'mes', 'cantidad_apoyan', 'total_padres'];
}
