<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    protected $table = 'aviso';
    
    protected $fillable = [
        'titulo', 
        'mensaje', 
        'autor_id', 
        'activo'
    ];

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'autor_id');
    }
}
