<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'usuario';
    protected $fillable = ['nombre_completo', 'email', 'password', 'rol_id', 'activo', 'email_verified_at'];
    protected $hidden = ['password', 'remember_token'];

    protected $guard_name = 'web';

    // Relación: Un usuario pertenece a un rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Relación: Un usuario puede ser un docente
    public function docente()
    {
        return $this->hasOne(Docente::class, 'usuario_id');
    }

    // Relación: Un usuario puede ser un alumno
    public function alumno()
    {
        return $this->hasOne(Alumno::class, 'usuario_id');
    }

}
