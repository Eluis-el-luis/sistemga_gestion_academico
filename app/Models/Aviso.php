<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aviso extends Model
{
    protected $table = 'aviso';

    protected $fillable = [
        'titulo',
        'mensaje',
        'autor_id',
        'activo',
        'modalidad_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autor_id');
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }
}