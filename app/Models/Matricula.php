<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matricula extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'matricula';

    protected $fillable = [
        'alumno_id',
        'aula_id',
        'anio_escolar_id',
        'estado',
        'fecha_matricula',
        'fecha_retiro',
    ];

    protected $casts = [
        'fecha_matricula' => 'date',
        'fecha_retiro' => 'date',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function boletines(): HasMany
    {
        return $this->hasMany(Boletin::class, 'matricula_id');
    }

    public function evaluacionesFamiliares(): HasMany
    {
        return $this->hasMany(EvaluacionFamiliar::class, 'matricula_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'matricula_id');
    }

    public function asistenciasAula(): HasMany
    {
        return $this->hasMany(AsistenciaAula::class, 'matricula_id');
    }

    public function asistenciasAsignatura(): HasMany
    {
        return $this->hasMany(AsistenciaAsignatura::class, 'matricula_id');
    }

    public function incidenciasDisciplinarias(): HasMany
    {
        return $this->hasMany(IncidenciaDisciplinaria::class, 'matricula_id');
    }

    public function examenesReparacion(): HasMany
    {
        return $this->hasMany(ExamenReparacion::class, 'matricula_id');
    }
}