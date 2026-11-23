<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorteEvaluativo extends Model
{
    use HasFactory;

    protected $table = 'corte_evaluativo';

    protected $fillable = [
        'anio_escolar_id',
        'numero',
        'semestre',
        'fecha_inicio',
        'fecha_fin',
        'peso_acumulado',
        'peso_examen',
    ];

    protected $casts = [
        'numero' => 'integer',
        'semestre' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'peso_acumulado' => 'integer',
        'peso_examen' => 'integer',
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'corte_evaluativo_id');
    }

    public function boletines(): HasMany
    {
        return $this->hasMany(Boletin::class, 'corte_evaluativo_id');
    }
}