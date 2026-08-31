<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaPersonal extends Model
{
    use HasFactory;

    protected $table = 'asistencia_personal';

    protected $fillable = [
        'usuario_id',
        'fecha',
        'hora_entrada',
        'estado',
        'observaciones'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}