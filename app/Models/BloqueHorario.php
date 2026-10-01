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
        'numero_bloque',
        'tipo_jornada',
        'nombre',
        'hora_inicio',
        'hora_fin',
        'es_recreo'
    ];

    // Agrega este bloque para castear los datos automáticamente
    protected $casts = [
        'es_recreo' => 'boolean',
    ];

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }
}