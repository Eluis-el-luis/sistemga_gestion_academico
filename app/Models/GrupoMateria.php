<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoMateria extends Model
{
    protected $table = 'grupo_materia';

    protected $fillable = ['nombre', 'orden'];

    public function asignaturas()
    {
        return $this->hasMany(Asignatura::class, 'grupo_materia_id');
    }
}