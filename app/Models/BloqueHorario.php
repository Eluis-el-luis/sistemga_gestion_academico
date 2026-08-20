<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueHorario extends Model
{
    use HasFactory;

    protected $table = 'bloque_horario';

    protected $fillable = [
        'modalidad_id',
        'turno',
        'nombre',
        'hora_inicio',
        'hora_fin',
        'es_recreo'
    ];

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }
}