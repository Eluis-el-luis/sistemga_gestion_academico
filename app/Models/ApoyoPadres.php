<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApoyoPadres extends Model
{
    protected $table = 'apoyo_padres';

    protected $fillable = [
        'aula_id',
        'mes',
        'cantidad_apoyan',
        'total_padres',
    ];

    protected $casts = [
        'mes' => 'integer',
        'cantidad_apoyan' => 'integer',
        'total_padres' => 'integer',
    ];

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }
}