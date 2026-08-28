<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaAula extends Model
{
    use HasFactory;

    protected $table = 'asistencia_aula'; 

    protected $fillable = [
        'matricula_id', 
        'fecha', 
        'estado_asistencia' 
    ];
    
    // Relación para traer los datos del estudiante matriculado
    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
}