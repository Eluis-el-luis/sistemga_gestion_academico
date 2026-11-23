<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCorteEvaluativoRequest;
use App\Models\AnioEscolar;
use App\Models\CorteEvaluativo;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class CorteEvaluativoController extends Controller
{
    public function index()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para configurar los parámetros de evaluación.');
        }

        try {
            $anioActivo = AnioEscolar::where('estado', 'activo')->first();
            $cortes = $anioActivo
                ? CorteEvaluativo::where('anio_escolar_id', $anioActivo->id)->orderBy('numero')->get()
                : collect();

            return view('academico.cortes.index', compact('cortes', 'anioActivo'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error al intentar cargar la configuración de los cortes evaluativos.'));
        }
    }

    public function update(UpdateCorteEvaluativoRequest $request, CorteEvaluativo $corte)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
            return back()->with('error', 'No tienes permisos para modificar los cortes evaluativos.');
        }

        try {
            $datos = $request->validated();
            $corte->update([
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'peso_acumulado' => $datos['peso_acumulado'] ?? $corte->peso_acumulado,
                'peso_examen' => $datos['peso_examen'] ?? $corte->peso_examen,
            ]);

            return back()->with('success', 'Parámetros del corte evaluativo actualizados correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un problema técnico al intentar actualizar los parámetros del corte.'));
        }
    }
}