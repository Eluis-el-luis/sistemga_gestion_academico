<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\BloqueHorario;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VisorHorarioController extends Controller
{
    use AuthorizesRequests;

    public function index(): View|RedirectResponse
    {
        try {
            return view('academico.visor.index');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el visor de horarios.'));
        }
    }

    public function docentes(): View|RedirectResponse
    {
        try {
            $docentes = Docente::with('usuario')
                ->get()
                ->sortBy(fn ($docente) => $docente->usuario->nombre_completo ?? 'Z');

            return view('academico.visor.docentes', compact('docentes'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el listado de docentes para el visor.'));
        }
    }

    public function horarioDocente(Docente $docente): View|RedirectResponse
    {
        try {
            $docente->load('usuario');

            $horarios = Horario::with(['bloqueHorario', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.aula.grado'])
                ->whereHas('aulaAsignaturaDocente', fn ($query) => $query->where('docente_id', $docente->id))
                ->get();

            $bloques = $horarios->map->bloqueHorario
                ->filter()
                ->unique('id')
                ->sortBy('hora_inicio');

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloques as $bloque) {
                $fila = ['bloque' => $bloque, 'dias' => []];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    $fila['dias'][$dia] = $horariosIndex[$bloque->id][$diaBD] ?? null;
                }
                $matriz[] = $fila;
            }

            return view('academico.visor.horario_docente', compact('docente', 'matriz', 'dias'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.visor.docentes')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el horario individual del docente.'));
        }
    }

    public function aulas(): View|RedirectResponse
    {
        try {
            $this->authorize('viewAny', Aula::class);

            /** @var Usuario $user */
            $user = Auth::user();
            $query = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                ->orderByDesc('anio_escolar_id')
                ->orderBy('grado_id');

            if ($user->hasAnyRole(['coordinador', 'Coordinador']) && !$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
                $docente = $user->docente;
                if ($docente && $docente->modalidad_coordina_id) {
                    $query->where('modalidad_id', $docente->modalidad_coordina_id);
                }
            }

            $aulas = $query->get();

            return view('academico.visor.aulas', compact('aulas'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el listado de aulas para el visor.'));
        }
    }

    public function horarioAula(Aula $aula): View|RedirectResponse
    {
        try {
            $this->authorize('view', $aula);

            $aula->load(['grado', 'modalidad', 'docenteGuia.usuario']);
            $asignaciones = AulaAsignaturaDocente::where('aula_id', $aula->id)->pluck('id');

            $horarios = Horario::with(['bloqueHorario', 'aulaAsignaturaDocente.asignatura', 'aulaAsignaturaDocente.docente.usuario'])
                ->whereIn('aula_asignatura_docente_id', $asignaciones)
                ->get();

            $bloques = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                ->where('turno', $aula->turno)
                ->where('tipo_jornada', 'Regular')
                ->orderBy('hora_inicio')
                ->get();

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloques as $bloque) {
                $fila = ['bloque' => $bloque, 'dias' => []];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    $fila['dias'][$dia] = $bloque->es_recreo ? null : ($horariosIndex[$bloque->id][$diaBD] ?? null);
                }
                $matriz[] = $fila;
            }

            return view('academico.visor.horario_aula', compact('aula', 'matriz', 'dias'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.visor.aulas')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el horario de esta aula.'));
        }
    }
}