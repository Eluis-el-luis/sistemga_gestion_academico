<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'usuario';

    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'activo',
        'email_verified_at',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    protected $guard_name = 'web';

    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class, 'usuario_id');
    }

    public function alumno(): HasOne
    {
        return $this->hasOne(Alumno::class, 'usuario_id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaPersonal::class, 'usuario_id');
    }

    public function avisos(): HasMany
    {
        return $this->hasMany(Aviso::class, 'autor_id');
    }
}