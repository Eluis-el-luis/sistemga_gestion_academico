<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Docente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'docente';

    protected $fillable = [
        'usuario_id',
        'codigo_unico_persona',
        'sexo',
        'modalidad_id',
        'es_coordinador',
        'modalidad_coordina_id',
    ];

    protected $casts = [
        'es_coordinador' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }

    public function modalidadCoordina(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_coordina_id');
    }

    public function aulasGuiadas(): HasMany
    {
        return $this->hasMany(Aula::class, 'docente_guia_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AulaAsignaturaDocente::class, 'docente_id');
    }
}