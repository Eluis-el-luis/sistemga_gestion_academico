<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Aula;
use App\Models\Alumno;
use Illuminate\Http\Request;

class DocenteGuiaController extends Controller
{
    public function misAlumnos()
    {
        $usuario = auth()->user();
        $docente = Docente::where('usuario_id', $usuario->id)->firstOrFail();
        
        // 1. Validar que tenga un aula asignada
        $aula = Aula::with('grado')->where('docente_guia_id', $docente->id)->first();
        
        if (!$aula) {
            return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como Maestro Guía.');
        }

        // 2. Traer alumnos con su matrícula activa y sus notas consolidadas
        $alumnos = Alumno::whereHas('matriculas', function($q) use ($aula) {
            $q->where('aula_id', $aula->id)->where('estado', 'activo');
        })->with(['matriculas' => function($q) use ($aula) {
            $q->where('aula_id', $aula->id)->where('estado', 'activo')->with('notas');
        }])->get();

        // 3. Variables para los KPIs de la cabecera
        $totalAlumnos = $alumnos->count();
        $aprobadosLimpios = 0;
        $enReparacion = 0;
        $riesgoCritico = 0;

        // 4. Lógica Matemática por Alumno
        foreach ($alumnos as $alumno) {
            $matricula = $alumno->matriculas->first();
            $notas = $matricula ? $matricula->notas : collect();

            if ($notas->count() > 0) {
                $alumno->promedio_global = round($notas->avg('nota_cuantitativa'), 2);
                // Asumimos que la nota mínima para aprobar es 60
                $alumno->clases_reprobadas = $notas->where('nota_cuantitativa', '<', 60)->count(); 
            } else {
                $alumno->promedio_global = 0;
                $alumno->clases_reprobadas = 0;
            }

            // Categorización Automática
            if ($alumno->clases_reprobadas == 0) {
                $aprobadosLimpios++;
                $alumno->estado_texto = 'Aprobado Limpio';
                $alumno->estado_color = 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20';
            } elseif ($alumno->clases_reprobadas <= 2) {
                $enReparacion++;
                $alumno->estado_texto = 'Reparación';
                $alumno->estado_color = 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20';
            } else {
                $riesgoCritico++;
                $alumno->estado_texto = 'Repitente / Crítico';
                $alumno->estado_color = 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20';
            }
        }

        // 5. Ordenar el ranking (Los mejores promedios primero)
        $alumnos = $alumnos->sortByDesc('promedio_global')->values();

        // 6. Porcentajes
        $pctAprobados = $totalAlumnos > 0 ? round(($aprobadosLimpios / $totalAlumnos) * 100) : 0;
        $pctReparacion = $totalAlumnos > 0 ? round(($enReparacion / $totalAlumnos) * 100) : 0;
        $pctRiesgo = $totalAlumnos > 0 ? round(($riesgoCritico / $totalAlumnos) * 100) : 0;

        return view('academico.docente-guia.mis-alumnos', compact(
            'aula', 'alumnos', 'totalAlumnos', 
            'aprobadosLimpios', 'pctAprobados', 
            'enReparacion', 'pctReparacion', 
            'riesgoCritico', 'pctRiesgo'
        ));
    }
}