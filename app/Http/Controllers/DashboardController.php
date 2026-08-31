<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // 1. KPIs Minimalistas (Solo para roles administrativos)
        $totalAlumnos = 0;
        $totalDocentes = 0;

        if ($user->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
            $totalAlumnos = Alumno::count();
            $totalDocentes = Docente::count();
        }
        
        // 2. Comunicados Oficiales (Para la campana de notificaciones)
        $avisos = DB::table('aviso')
                    ->join('usuario', 'aviso.autor_id', '=', 'usuario.id')
                    ->select('aviso.*', 'usuario.nombre_completo as autor_nombre')
                    ->where('aviso.activo', true)
                    ->orderBy('aviso.created_at', 'desc')
                    ->take(5)
                    ->get();

        // 3. Extracción de Datos Reales para Gráficas (Por Modalidad)
        $nombresModalidades = [];
        $rendimientoData = [];
        $asistenciaAlumnosData = [];
        $asistenciaDocentesData = [];

        if ($user->hasAnyRole(['Director', 'Subdirector'])) {
            $modalidades = DB::table('modalidad')->orderBy('id')->get();
            
            foreach($modalidades as $mod) {
                $nombresModalidades[] = $mod->nombre;

                // A. Rendimiento (Promedio de nota_cuantitativa)
                $promedioNota = DB::table('nota')
                    ->join('aula_asignatura_docente', 'nota.aula_asignatura_docente_id', '=', 'aula_asignatura_docente.id')
                    ->join('aula', 'aula_asignatura_docente.aula_id', '=', 'aula.id')
                    ->where('aula.modalidad_id', $mod->id)
                    ->avg('nota.nota_cuantitativa') ?? 0;
                $rendimientoData[] = round($promedioNota, 1);

                // B. Asistencia Alumnos (% de 'Presente')
                $totalAsistencias = DB::table('asistencia_aula')
                    ->join('matricula', 'asistencia_aula.matricula_id', '=', 'matricula.id')
                    ->join('aula', 'matricula.aula_id', '=', 'aula.id')
                    ->where('aula.modalidad_id', $mod->id)
                    ->count();
                    
                $presentesAlumnos = DB::table('asistencia_aula')
                    ->join('matricula', 'asistencia_aula.matricula_id', '=', 'matricula.id')
                    ->join('aula', 'matricula.aula_id', '=', 'aula.id')
                    ->where('aula.modalidad_id', $mod->id)
                    ->whereIn('estado_asistencia', ['Presente', 'presente', 'Asistio', 'P']) 
                    ->count();
                    
                $asistenciaAlumnosData[] = $totalAsistencias > 0 ? round(($presentesAlumnos / $totalAsistencias) * 100, 1) : 0;

                // C. Asistencia Docentes (% de presente = true)
                $totalAsistenciasDoc = DB::table('asistencia_docente')
                    ->join('aula_asignatura_docente', 'asistencia_docente.aula_asignatura_docente_id', '=', 'aula_asignatura_docente.id')
                    ->join('aula', 'aula_asignatura_docente.aula_id', '=', 'aula.id')
                    ->where('aula.modalidad_id', $mod->id)
                    ->count();
                    
                $presentesDoc = DB::table('asistencia_docente')
                    ->join('aula_asignatura_docente', 'asistencia_docente.aula_asignatura_docente_id', '=', 'aula_asignatura_docente.id')
                    ->join('aula', 'aula_asignatura_docente.aula_id', '=', 'aula.id')
                    ->where('aula.modalidad_id', $mod->id)
                    ->where('presente', true)
                    ->count();

                $asistenciaDocentesData[] = $totalAsistenciasDoc > 0 ? round(($presentesDoc / $totalAsistenciasDoc) * 100, 1) : 0;
            }
        }

        // 4. Horario Semanal si es Docente
        $horarios = collect();
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

        if ($user->hasRole('Docente por Asignatura') && $user->docente) {
            $docenteId = $user->docente->id;
            
            $horarios = \App\Models\Horario::with([
                    'bloqueHorario', 
                    'aulaAsignaturaDocente.asignatura', 
                    'aulaAsignaturaDocente.aula.grado', 
                    'aulaAsignaturaDocente.aula.modalidad'
                ])
                ->whereHas('aulaAsignaturaDocente', function($q) use ($docenteId) {
                    $q->where('docente_id', $docenteId);
                })
                ->get()
                ->sortBy(function($horario) {
                    return $horario->bloqueHorario->hora_inicio;
                })
                ->groupBy('dia_semana');
        }

        return view('dashboard', compact(
            'totalAlumnos', 'totalDocentes', 'avisos', 'horarios', 'diasSemana',
            'nombresModalidades', 'rendimientoData', 'asistenciaAlumnosData', 'asistenciaDocentesData'
        ));
    }

    public function storeAviso(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) { abort(403); }

        $request->validate(['titulo' => 'required|string|max:120', 'mensaje' => 'required|string|max:1000']);
        DB::table('aviso')->insert([
            'titulo' => $request->titulo, 'mensaje' => $request->mensaje, 'autor_id' => Auth::id(),
            'activo' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Aviso publicado correctamente.');
    }

    public function updateAviso(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) { abort(403); }

        $request->validate(['titulo' => 'required|string|max:120', 'mensaje' => 'required|string|max:1000']);
        DB::table('aviso')->where('id', $id)->update(['titulo' => $request->titulo, 'mensaje' => $request->mensaje, 'updated_at' => now()]);

        return redirect()->route('dashboard')->with('success', 'Aviso actualizado correctamente.');
    }

    public function destroyAviso($id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Director', 'Subdirector'])) { abort(403); }

        DB::table('aviso')->where('id', $id)->delete();
        return redirect()->route('dashboard')->with('success', 'Aviso eliminado del sistema.');
    }
}