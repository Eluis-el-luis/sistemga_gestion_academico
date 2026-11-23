<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoMateria extends Model
{
    protected $table = 'grupo_materia';

    protected $fillable = [
        'nombre',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function asignaturas(): HasMany
    {
        return $this->hasMany(Asignatura::class, 'grupo_materia_id');
    }
}