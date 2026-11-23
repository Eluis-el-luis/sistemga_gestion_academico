<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MallaCurricular extends Model
{
    use HasFactory;

    protected $table = 'malla_curricular';

    protected $fillable = [
        'grado_id',
        'asignatura_id',
        'horas_semanales_sugeridas',
        'activo',
    ];

    protected $casts = [
        'horas_semanales_sugeridas' => 'decimal:1',
        'activo' => 'boolean',
    ];

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class, 'asignatura_id');
    }
}