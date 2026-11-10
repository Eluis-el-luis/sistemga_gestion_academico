<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\AnioEscolar;
use App\Http\Requests\StoreMatriculaRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MatriculaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Matricula::class);

            $anioActivo = AnioEscolar::where('estado', 'activo')->first();
            $aniosEscolares = AnioEscolar::orderByDesc('fecha_inicio')->get(); // Para el filtro de años
            $query = Matricula::with(['alumno', 'aula.grado', 'aula.modalidad', 'anioEscolar']);

            // --- FILTRO DE AÑO ---
            if ($request->filled('anio_escolar_id')) {
                $query->where('anio_escolar_id', $request->anio_escolar_id);
            } elseif (!$request->has('anio_escolar_id') && $anioActivo) {
                $query->where('anio_escolar_id', $anioActivo->id);
            }

            // --- BÚSQUEDA INTELIGENTE (ILIKE para PostgreSQL) ---
            if ($request->filled('buscar')) {
                $busqueda = $request->buscar;
                $query->whereHas('alumno', function($q) use ($busqueda) {
                    $q->where('nombre_completo', 'ilike', "%{$busqueda}%")
                      ->orWhere('codigo_unico_persona', 'ilike', "%{$busqueda}%");
                });
            }

            if ($request->filled('aula_id')) {
                $query->where('aula_id', $request->aula_id);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            // Aplicar scope de ownership a la consulta principal
            $query = $this->applyMatriculaScope($query, auth()->user());

            $matriculas = $query->latest('fecha_matricula')->paginate(15);

            // Aulas para el filtro - también con scope
            $aulas = $this->getAulasScope(auth()->user());

            return view('academico.matriculas.index', compact('matriculas', 'aniosEscolares', 'aulas', 'anioActivo'));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al intentar cargar el listado de matrículas.');
        }
    }

    public function create(Request $request)
    {
        try {
            $this->authorize('create', Matricula::class);

            $anioActivo = AnioEscolar::where('estado', 'activo')->first();
            
            // 🔒 BLOQUEO DE DOBLE MATRÍCULA: 
            $alumnos = Alumno::whereDoesntHave('matriculas', function($q) use ($anioActivo) {
                if ($anioActivo) {
                    $q->where('anio_escolar_id', $anioActivo->id);
                }
            })->orderBy('nombre_completo')->get();

            // 📊 CONTROL REAL DE CUPOS: Añadimos el conteo de matrículas activas por aula
            $aulas = Aula::with(['grado', 'modalidad'])
                ->withCount(['matriculas' => function($query) use ($anioActivo) {
                    $query->where('estado', 'activo');
                    if ($anioActivo) {
                        $query->where('anio_escolar_id', $anioActivo->id);
                    }
                }])->get();

            $anios = AnioEscolar::where('estado', 'activo')->get();
            
            $alumnoSeleccionado = $request->query('alumno_id');

            return view('academico.matriculas.create', compact('alumnos', 'aulas', 'anios', 'alumnoSeleccionado'));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.matriculas.index')->with('error', 'No se pudo abrir el formulario de matrícula.');
        }
    }

    public function store(StoreMatriculaRequest $request)
    {
        $datosValidados = $request->validated();

        try {
            $this->authorize('create', Matricula::class);
            
            // Verificamos el cupo antes de guardar para evitar sobrecupos lógicos
            $aula = Aula::withCount(['matriculas' => function($query) use ($datosValidados) {
                $query->where('estado', 'activo')
                      ->where('anio_escolar_id', $datosValidados['anio_escolar_id'] ?? null);
            }])->findOrFail($datosValidados['aula_id']);

            if ($aula->matriculas_count >= $aula->cupo) {
                return back()->withInput()->with('error', 'Lo sentimos, esta aula ha alcanzado su cupo máximo (' . $aula->cupo . ').');
            }

            Matricula::create($datosValidados);

            // UX MEJORADA: Redirigimos de vuelta al formulario de creación para agilizar matrículas masivas 
            // con un mensaje de éxito, evitando mandarte directo al historial general.
            return redirect()->route('academico.matriculas.create')
                             ->with('success', '¡Estudiante matriculado exitosamente! Puedes registrar al siguiente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'Ocurrió un problema técnico al procesar la matrícula en la base de datos.');
        }
    }
    
    public function retirar(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('update', $matricula);
            
            $matricula->update(['estado' => 'retirado']);
            
            return redirect()->route('academico.matriculas.index')->with('success', 'Estudiante retirado correctamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->with('error', 'No se pudo actualizar el estado del estudiante a retirado.');
        }
    }

    public function reactivar(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('update', $matricula);
            
            $matricula->update(['estado' => 'activo']);
            
            return redirect()->route('academico.matriculas.index')->with('success', 'Matrícula reactivada correctamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->with('error', 'No se pudo reactivar la matrícula debido a un error de conexión.');
        }
    }

    public function destroy(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('delete', $matricula);
            
            $matricula->delete();
            
            return redirect()->route('academico.matriculas.index')->with('success', 'Matrícula eliminada (borrado lógico).');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->with('error', 'No se pudo eliminar la matrícula. Es posible que el sistema la proteja por tener calificaciones asociadas.');
        }
    }

    /**
     * Scope centralizado para filtrar matrículas según ownership del usuario
     */
    protected function applyMatriculaScope($query, \App\Models\Usuario $usuario)
    {
        // Super-admin (Director/Subdirector/Gestor/Secretaria) ven todo
        if ($usuario->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios', 'Secretaria'])) {
            return $query;
        }

        // Coordinador: solo matrículas de su modalidad
        if ($usuario->hasRole('Coordinador')) {
            $docente = $usuario->docente;
            if ($docente && $docente->es_coordinador && $docente->modalidad_coordina_id) {
                return $query->whereHas('aula', function($q) use ($docente) {
                    $q->where('modalidad_id', $docente->modalidad_coordina_id);
                });
            }
            return $query;
        }

        // Docente Guía: solo matrículas de su aula
        if ($usuario->hasRole('Docente Guia')) {
            $docente = $usuario->docente;
            if ($docente) {
                return $query->where('aula_id', function($subQ) use ($docente) {
                    $subQ->select('id')->from('aula')->where('docente_guia_id', $docente->id);
                });
            }
            return $query;
        }

        // Docente por Asignatura: matrículas de aulas donde imparte
        if ($usuario->hasRole('Docente por Asignatura')) {
            $docente = $usuario->docente;
            if ($docente) {
                return $query->whereHas('aula.asignaciones', function($q) use ($docente) {
                    $q->where('docente_id', $docente->id)->where('activo', true);
                });
            }
            return $query;
        }

        return $query;
    }

    /**
     * Obtener aulas para el filtro dropdown según ownership
     */
    protected function getAulasScope(\App\Models\Usuario $usuario)
    {
        $query = \App\Models\Aula::with(['grado', 'modalidad']);

        if ($usuario->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios', 'Secretaria'])) {
            return $query->get();
        }

        if ($usuario->hasRole('Coordinador')) {
            $docente = $usuario->docente;
            if ($docente && $docente->es_coordinador && $docente->modalidad_coordina_id) {
                return $query->where('modalidad_id', $docente->modalidad_coordina_id)->get();
            }
            return $query->get();
        }

        if ($usuario->hasRole('Docente Guia')) {
            $docente = $usuario->docente;
            if ($docente) {
                return $query->where('docente_guia_id', $docente->id)->get();
            }
            return $query->get();
        }

        if ($usuario->hasRole('Docente por Asignatura')) {
            $docente = $usuario->docente;
            if ($docente) {
                return $query->whereHas('asignaciones', function($q) use ($docente) {
                    $q->where('docente_id', $docente->id)->where('activo', true);
                })->get();
            }
            return $query->get();
        }

        return $query->get();
    }
}