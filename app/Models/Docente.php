<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;
    protected $table = 'docente';
    protected $fillable = ['usuario_id', 'codigo_unico_persona', 'sexo', 'es_coordinador', 'modalidad_coordina_id'];


    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
    // Relación: Modalidad que coordina (si aplica)
    public function modalidadCoordina()
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_coordina_id');
    }

    // Relación: Aulas donde es docente guía
    public function aulasGuiadas()
    {
        return $this->hasMany(Aula::class, 'docente_guia_id');
    }

}