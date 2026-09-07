<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReparacionController extends Controller
{
        public function index()
    {
        $this->authorize('viewAny', \App\Models\Matricula::class);
        $anioActivo = \App\Models\AnioEscolar::where('activo', true)->first();
        
        $gradosAutoPromovidos = [1, 2, 3, 4, 5]; 

        $matriculasRaw = \App\Models\Matricula::with(['alumno', 'aula.grado'])
            ->where('anio_escolar_id', $anioActivo->id)
            ->where('estado', 'activo')
            ->whereHas('aula', function($q) use ($gradosAutoPromovidos) {
                $q->whereNotIn('grado_id', $gradosAutoPromovidos);
            })
            ->get();

        $matriculas = $matriculasRaw->map(function ($matricula) {
            // Esta línea es la que evita el error nulo en la vista
            $matricula->clases_reprobadas = \App\Models\Nota::with('aulaAsignaturaDocente.asignatura')
                ->where('matricula_id', $matricula->id)
                ->whereNotNull('nota_cuantitativa')
                ->where('nota_cuantitativa', '<', 60)
                ->get(); 
            
            return $matricula;
        })
        ->filter(function ($matricula) {
            return $matricula->clases_reprobadas->count() > 0;
        })
        ->values();

        return view('academico.reparacion.index', compact('matriculas'));
    }
}
