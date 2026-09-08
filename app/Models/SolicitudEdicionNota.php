<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudEdicionNota extends Model
{
    protected $table = 'solicitud_edicion_nota';

    protected $fillable = [
        'docente_id',
        'nota_id',
        'motivo',
        'estado',
        'autorizado_por',
        'fecha_resolucion',
    ];

    protected $casts = [
        'fecha_resolucion' => 'datetime',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function nota()
    {
        return $this->belongsTo(Nota::class, 'nota_id');
    }

    public function autorizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por');
    }
}