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

            $anioActivo = AnioEscolar::where('activo', true)->first();
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

            $matriculas = $query->latest('fecha_matricula')->paginate(15);

            $aniosEscolares = AnioEscolar::orderBy('nombre', 'desc')->get();
            $aulas = Aula::with(['grado', 'modalidad'])->get();

            return view('academico.matriculas.index', compact('matriculas', 'aniosEscolares', 'aulas', 'anioActivo'));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla alguna relación o la consulta con ILIKE
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al intentar cargar el listado de matrículas.');
        }
    }

    public function create(Request $request)
    {
        try {
            $this->authorize('create', Matricula::class);

            $anioActivo = AnioEscolar::where('activo', true)->first();
            
            // 🔒 BLOQUEO DE DOBLE MATRÍCULA: 
            // Solo traemos alumnos que NO tengan matrícula en el año activo
            $alumnos = Alumno::whereDoesntHave('matriculas', function($q) use ($anioActivo) {
                if ($anioActivo) {
                    $q->where('anio_escolar_id', $anioActivo->id);
                }
            })->orderBy('nombre_completo')->get();

            $aulas = Aula::with(['grado', 'modalidad'])->get();
            $anios = AnioEscolar::where('activo', true)->get();
            
            $alumnoSeleccionado = $request->query('alumno_id');

            return view('academico.matriculas.create', compact('alumnos', 'aulas', 'anios', 'alumnoSeleccionado'));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla la carga del formulario de inscripción
            return redirect()->route('academico.matriculas.index')->with('error', 'No se pudo abrir el formulario de matrícula.');
        }
    }

    public function store(StoreMatriculaRequest $request)
    {
        // La validación del StoreMatriculaRequest se ejecuta de forma segura antes de entrar aquí
        $datosValidados = $request->validated();

        try {
            $this->authorize('create', Matricula::class);
            
            Matricula::create($datosValidados);

            return redirect()->route('academico.matriculas.index')
                             ->with('success', 'Matrícula procesada exitosamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si la base de datos rechaza la creación de la matrícula
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
            // CONTINGENCIA: Si falla el cambio de estado
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
            // CONTINGENCIA: Si la base de datos rechaza la reactivación
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
            // CONTINGENCIA: Si el registro tiene dependencias que impiden borrarlo
            return back()->with('error', 'No se pudo eliminar la matrícula. Es posible que el sistema la proteja por tener calificaciones asociadas.');
        }
    }
}