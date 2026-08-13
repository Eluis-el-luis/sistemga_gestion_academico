<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;
    protected $table = 'alumno';
    protected $fillable = ['usuario_id', 'codigo_unico_persona', 'nombre_completo', 'sexo', 'fecha_nacimiento'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación: Un alumno tiene muchas matrículas a lo largo de los años
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'alumno_id');
    }
}