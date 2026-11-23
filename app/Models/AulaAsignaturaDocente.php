<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AulaAsignaturaDocente extends Model
{
    use HasFactory;

    protected $table = 'aula_asignatura_docente';

    protected $fillable = [
        'aula_id',
        'asignatura_id',
        'docente_id',
        'anio_escolar_id',
        'horas_semanales',
        'activo',
    ];

    protected $casts = [
        'horas_semanales' => 'decimal:1',
        'activo' => 'boolean',
    ];

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class, 'asignatura_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'aula_asignatura_docente_id');
    }

    public function cortesCerrados(): HasMany
    {
        return $this->hasMany(CorteCerrado::class, 'aula_asignatura_docente_id');
    }

    public function actividadesEvaluativas(): HasMany
    {
        return $this->hasMany(ActividadEvaluativa::class, 'aula_asignatura_docente_id');
    }

    public function avancesContenido(): HasMany
    {
        return $this->hasMany(AvanceContenido::class, 'aula_asignatura_docente_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'aula_asignatura_docente_id');
    }
}