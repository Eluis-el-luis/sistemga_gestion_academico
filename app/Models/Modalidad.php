<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modalidad extends Model
{
    use HasFactory;

    protected $table = 'modalidad';

    protected $fillable = [
        'nombre',
    ];

    public function grados(): HasMany
    {
        return $this->hasMany(Grado::class, 'modalidad_id');
    }

    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'modalidad_id');
    }

    public function bloquesHorario(): HasMany
    {
        return $this->hasMany(BloqueHorario::class, 'modalidad_id');
    }
}