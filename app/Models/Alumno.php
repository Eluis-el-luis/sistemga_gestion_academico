<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alumno';

    protected $fillable = [
        'usuario_id',
        'codigo_unico_persona',
        'nombre_completo',
        'sexo',
        'fecha_nacimiento',
        'direccion_domiciliar',
        'enfermedades_cronicas',
        'hermanos_en_colegio',
        'madre_nombre_completo',
        'madre_cedula',
        'madre_telefono',
        'madre_ocupacion',
        'madre_asiste_iglesia',
        'madre_nombre_iglesia',
        'padre_nombre_completo',
        'padre_cedula',
        'padre_telefono',
        'padre_ocupacion',
        'padre_asiste_iglesia',
        'padre_nombre_iglesia',
        'tutor_nombre_completo',
        'tutor_cedula',
        'tutor_telefono',
        'tutor_ocupacion',
        'autorizado_retirar_nombre',
        'autorizado_retirar_cedula',
        'autorizado_retirar_telefono',
        'acepta_compromiso_cristiano',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'madre_asiste_iglesia' => 'boolean',
        'padre_asiste_iglesia' => 'boolean',
        'acepta_compromiso_cristiano' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'alumno_id');
    }
}