<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaAula extends Model
{
    use HasFactory;

    protected $table = 'asistencia_aula';

    protected $fillable = [
        'matricula_id',
        'fecha',
        'estado_asistencia',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
}