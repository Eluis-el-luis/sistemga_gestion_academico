<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicadorLogro extends Model
{
    use HasFactory;

    protected $table = 'indicador_logro';

    protected $fillable = [
        'codigo',
        'nombre',
        'nota_min',
        'nota_max',
        'modalidad_id',
        'grado_min',
        'grado_max',
    ];

    protected $casts = [
        'nota_min' => 'decimal:2',
        'nota_max' => 'decimal:2',
    ];

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }

    public function gradoMin(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_min');
    }

    public function gradoMax(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_max');
    }
}