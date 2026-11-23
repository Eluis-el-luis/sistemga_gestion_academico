<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'es_recreo',
    ];

    protected $casts = [
        'numero_bloque' => 'integer',
        'es_recreo' => 'boolean',
    ];

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'bloque_horario_id');
    }
}