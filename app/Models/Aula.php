<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aula';

    protected $fillable = [
        'nombre',
        'grado_id',
        'modalidad_id',
        'turno',
        'docente_guia_id',
        'anio_escolar_id',
        'cupo',
    ];

    protected $casts = [
        'cupo' => 'integer',
    ];

    public function docenteGuia(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_guia_id');
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'aula_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AulaAsignaturaDocente::class, 'aula_id');
    }

    public function apoyosPadres(): HasMany
    {
        return $this->hasMany(ApoyoPadres::class, 'aula_id');
    }
}