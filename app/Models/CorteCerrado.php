<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteCerrado extends Model
{
    protected $table = 'corte_cerrado';

    protected $fillable = [
        'aula_asignatura_docente_id',
        'corte_evaluativo_id',
        'bloqueado',
        'cerrado_por',
        'fecha_cierre',
    ];

    protected $casts = [
        'bloqueado' => 'boolean',
        'fecha_cierre' => 'datetime',
    ];

    public function aulaAsignaturaDocente()
    {
        return $this->belongsTo(AulaAsignaturaDocente::class, 'aula_asignatura_docente_id');
    }

    public function corteEvaluativo()
    {
        return $this->belongsTo(CorteEvaluativo::class, 'corte_evaluativo_id');
    }

    public function cerradoPor()
    {
        return $this->belongsTo(Usuario::class, 'cerrado_por');
    }
}