<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    use HasFactory;
    protected $table = 'grado';
    protected $fillable = ['nombre', 'modalidad_id', 'horas_maximas_semanales',];

    public function mallaCurricular()
    {
        return $this->hasMany(MallaCurricular::class, 'grado_id');
    }

    /**
     * Relación: Un grado pertenece a una modalidad.
     */
    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }
}