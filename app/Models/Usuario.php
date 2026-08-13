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
}
