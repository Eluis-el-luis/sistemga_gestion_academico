<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignatura extends Model
{
    use HasFactory;

    protected $table = 'asignatura';

    protected $fillable = [
        'nombre',
        'area',
        'grupo_materia_id',
        'es_extracurricular',
    ];

    protected $casts = [
        'es_extracurricular' => 'boolean',
    ];

    public function grupoMateria(): BelongsTo
    {
        return $this->belongsTo(GrupoMateria::class, 'grupo_materia_id');
    }

    public function mallasCurriculares(): HasMany
    {
        return $this->hasMany(MallaCurricular::class, 'asignatura_id');
    }

    public function asignacionesAula(): HasMany
    {
        return $this->hasMany(AulaAsignaturaDocente::class, 'asignatura_id');
    }
}