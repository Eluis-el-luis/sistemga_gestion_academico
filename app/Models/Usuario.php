<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; 
use Laravel\Sanctum\HasApiTokens;      

class Usuario extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable; 

    protected $table = 'usuario'; 
    protected $fillable = ['nombre_completo', 'email', 'password', 'rol_id', 'activo'];
    protected $hidden = ['password'];

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
