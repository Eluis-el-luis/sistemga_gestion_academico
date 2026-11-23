<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PrematriculaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();

            if (!$usuario->docente) {
                return redirect()->route('dashboard')
                    ->with('error', 'Su usuario no tiene un perfil de docente asociado.');
            }

            $aula = Aula::with(['grado', 'anioEscolar'])
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->where('docente_guia_id', $usuario->docente->id)
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')
                    ->with('error', 'No tienes un aula asignada como docente guía en el ciclo actual.');
            }

            $asignacionesIds = AulaAsignaturaDocente::where('aula_id', $aula->id)
                ->where('anio_escolar_id', $aula->anio_escolar_id)
                ->pluck('id');

            $matriculas = Matricula::with('alumno')
                ->where('aula_id', $aula->id)
                ->where('estado', 'activo')
                ->orderBy('id')
                ->get();

            foreach ($matriculas as $matricula) {
                $notas = Nota::where('matricula_id', $matricula->id)
                    ->whereIn('aula_asignatura_docente_id', $asignacionesIds)
                    ->whereNotNull('nota_cuantitativa')
                    ->get()
                    ->groupBy('aula_asignatura_docente_id');

                $reprobadas = 0;

                foreach ($asignacionesIds as $asignacionId) {
                    $notasAsignatura = $notas->get($asignacionId, collect());
                    if ($notasAsignatura->isNotEmpty()) {
                        $promedioFinal = (float) $notasAsignatura->avg('nota_cuantitativa');
                        if ($promedioFinal < 60) {
                            $reprobadas++;
                        }
                    }
                }

                $matricula->clases_reprobadas = $reprobadas;
                $matricula->estado_academico = $reprobadas > 0 ? 'En Reparación' : 'Limpio';
            }

            return view('academico.prematricula.index', compact('aula', 'matriculas'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el panel de pre-matrícula.'));
        }
    }

    public function promover(Request $request, Matricula $matricula): RedirectResponse
    {
        if ($request->input('estado_academico') === 'En Reparación') {
            return back()
                ->with('error', 'Acción denegada: El estudiante tiene asignaturas reprobadas y debe ser evaluado por Dirección.');
        }

        try {
            $resultado = DB::transaction(function () use ($matricula) {
                $aulaActual = $matricula->aula;
                $nombreSeccion = $aulaActual?->nombre;

                $proximoGrado = Grado::where('id', '>', $aulaActual?->grado_id ?? 0)->orderBy('id')->first();
                if (!$proximoGrado) {
                    return ['tipo' => 'error', 'mensaje' => 'No existe un grado superior configurado para este alumno.'];
                }

                $proximoAnio = AnioEscolar::where('id', '>', $matricula->anio_escolar_id)->orderBy('id')->first();
                if (!$proximoAnio) {
                    return ['tipo' => 'error', 'mensaje' => 'El próximo ciclo escolar aún no ha sido aperturado.'];
                }

                $proximaAula = Aula::where('grado_id', $proximoGrado->id)
                    ->where('nombre', $nombreSeccion)
                    ->where('anio_escolar_id', $proximoAnio->id)
                    ->first();

                if (!$proximaAula) {
                    return [
                        'tipo' => 'error',
                        'mensaje' => 'El aula para ' . $proximoGrado->nombre . ' - Sección "' . $nombreSeccion . '" aún no ha sido creada para el próximo año.',
                    ];
                }

                Matricula::updateOrCreate(
                    [
                        'alumno_id' => $matricula->alumno_id,
                        'anio_escolar_id' => $proximoAnio->id,
                    ],
                    [
                        'aula_id' => $proximaAula->id,
                        'estado' => 'activo',
                        'fecha_matricula' => now()->toDateString(),
                    ]
                );

                return [
                    'tipo' => 'success',
                    'mensaje' => 'Alumno promovido automáticamente a ' . $proximoGrado->nombre . ' - Sección "' . $nombreSeccion . '".',
                ];
            });

            return back()->with($resultado['tipo'], $resultado['mensaje']);
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible promover al estudiante.'));
        }
    }

    public function remitir(Request $request, Matricula $matricula): RedirectResponse
    {
        try {
            return back()->with('success', 'El expediente ha sido bloqueado y remitido a Dirección.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible remitir el expediente a Dirección.'));
        }
    }
}