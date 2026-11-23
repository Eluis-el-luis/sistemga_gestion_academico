<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    use HasFactory;

    protected $table = 'grado';

    protected $fillable = [
        'nombre',
        'modalidad_id',
        'horas_maximas_semanales',
    ];

    protected $casts = [
        'horas_maximas_semanales' => 'integer',
    ];

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }

    public function mallaCurricular(): HasMany
    {
        return $this->hasMany(MallaCurricular::class, 'grado_id');
    }

    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'grado_id');
    }
}