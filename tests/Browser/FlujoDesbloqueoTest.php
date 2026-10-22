<?php

namespace Tests\Browser;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Asignatura;
use App\Models\CorteCerrado;
use App\Models\CorteEvaluativo;
use App\Models\Matricula;
use App\Models\SolicitudEdicionNota;
use Laravel\Dusk\Browser;

/**
 * E2E 3 — FLUJO DE BLOQUEO Y DESBLOQUEO DE NOTAS (auditoría académica).
 *
 * Docente: registra notas → cierra el parcial → solicita desbloqueo con motivo.
 * Dirección: aprueba la solicitud → el parcial se reabre → el docente corrige
 * la nota. Se prueba también el rechazo (el parcial permanece cerrado).
 */
class FlujoDesbloqueoTest extends E2eTestCase
{
    /**
     * Prepara actividad + notas consolidadas + cierre del parcial para la
     * asignación dada. (Vía modelos: el registro por UI ya se cubre en otros
     * flujos.) Devuelve el id de la actividad creada.
     */
    private function prepararCorteCerrado(AulaAsignaturaDocente $asignacion, int $corteId): int
    {
        $actividad = \App\Models\ActividadEvaluativa::create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $corteId,
            'nombre' => 'Prueba 1',
            'tipo' => 'acumulado',
            'puntaje_maximo' => 50,
            'fecha' => now()->toDateString(),
        ]);

        $matriculas = Matricula::where('aula_id', $asignacion->aula_id)
            ->where('estado', 'activo')->orderBy('id')->get();

        $servicio = app(\App\Services\NotaService::class);

        foreach ($matriculas as $i => $matricula) {
            $puntos = [45, 35, 25][$i % 3];

            \App\Models\NotaActividad::create([
                'matricula_id' => $matricula->id,
                'actividad_evaluativa_id' => $actividad->id,
                'nota_obtenida' => $puntos,
            ]);

            // Nota consolidada en escala MINED (45/35/25 puntos sobre 100 => AI).
            $servicio->registrarNotaFinal($matricula->id, $asignacion->id, $corteId, $puntos, 100, null);
        }

        // Cerrar el parcial por la misma vía del controlador.
        CorteCerrado::create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $corteId,
            'bloqueado' => true,
        ]);

        return $actividad->id;
    }

    public function test_docente_solicita_desbloqueo_y_direccion_aprueba(): void
    {
        $aula = Aula::where('nombre', '1ro A')->firstOrFail();
        $matematicaId = Asignatura::where('nombre', 'Matemática')->value('id');
        $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)
            ->where('asignatura_id', $matematicaId)->firstOrFail();
        $corteId = CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)
            ->where('numero', 1)->value('id');

        $this->prepararCorteCerrado($asignacion, $corteId);
        $motivo = 'Error de digitacion en la Tarea 1 de Juan Perez';

        $this->browse(function (Browser $browser) use ($asignacion, $corteId, $motivo) {
            // ---------- 1. DOCENTE: solicita el desbloqueo (modal Alpine) ----------
            $this->loginComo($browser, self::EMAIL_DOCENTE_MATERIA)
                ->visit("/academico/notas/planilla/{$asignacion->id}?corte_evaluativo_id={$corteId}");

            $this->abrirModal($browser, 'abrir-modal-desbloqueo');

            $browser->type('motivo', $motivo)
                ->click('form[action*="solicitar-desbloqueo"] button[type="submit"]')
                ->waitForText('Solicitud enviada', 15);

            $this->assertDatabaseHas('solicitud_edicion_nota', [
                'motivo' => $motivo,
                'estado' => 'Pendiente',
            ]);

            // ---------- 2. DIRECCIÓN: revisa y aprueba ----------
            $this->logoutDe($browser);
            $this->loginComo($browser, self::EMAIL_DIRECTOR)
                ->visit('/academico/notas/solicitudes')
                ->assertSee($motivo)
                ->click('form[action*="aprobar"] button[type="submit"]')
                ->waitForText('Solicitud aprobada', 15);

            $this->assertSame(
                1,
                SolicitudEdicionNota::where('estado', 'Aprobada')->count(),
                'La solicitud debe quedar aprobada.'
            );
            $this->assertSame(
                0,
                CorteCerrado::where('aula_asignatura_docente_id', $asignacion->id)
                    ->where('corte_evaluativo_id', $corteId)->count(),
                'Aprobar debe eliminar el bloqueo del parcial.'
            );

            // ---------- 3. DOCENTE: corrige la nota con el parcial reabierto ----------
            $this->logoutDe($browser);
            $this->loginComo($browser, self::EMAIL_DOCENTE_MATERIA);

            $actividadId = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('nombre', 'Prueba 1')->value('id');
            $matriculas = Matricula::where('aula_id', $asignacion->aula_id)
                ->where('estado', 'activo')->orderBy('id')->get();

            $browser->visit("/academico/notas/evaluar/{$asignacion->id}?corte_evaluativo_id={$corteId}")
                ->type("notas[{$matriculas[2]->id}][{$actividadId}]", '50')
                ->click('#form-calificaciones button[type="submit"]')
                ->waitForText('Calificaciones actualizadas', 15);

            // Corrección guardada: 50 puntos sobre total oficial 100 => NF 50.
            $nota = \App\Models\Nota::where('matricula_id', $matriculas[2]->id)
                ->where('corte_evaluativo_id', $corteId)
                ->where('aula_asignatura_docente_id', $asignacion->id)->first();
            $this->assertNotNull($nota, 'La nota corregida debe existir tras el desbloqueo.');
            $this->assertEqualsWithDelta(50, (float) $nota->nota_cuantitativa, 0.01);
        });
    }

    public function test_direccion_rechaza_solicitud_y_el_parcial_permace_cerrado(): void
    {
        $aula = Aula::where('nombre', '1ro A')->firstOrFail();
        $lenguaId = Asignatura::where('nombre', 'Lengua y Literatura')->value('id');
        $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)
            ->where('asignatura_id', $lenguaId)->firstOrFail();
        $corteId = CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)
            ->where('numero', 1)->value('id');

        $this->prepararCorteCerrado($asignacion, $corteId);

        $this->browse(function (Browser $browser) use ($asignacion, $corteId) {
            // ---------- 1. DOCENTE GUÍA (dueño de Lengua): solicita (modal) ----------
            $this->loginComo($browser, self::EMAIL_DOCENTE_GUIA)
                ->visit("/academico/notas/planilla/{$asignacion->id}?corte_evaluativo_id={$corteId}");

            $this->abrirModal($browser, 'abrir-modal-desbloqueo');

            $browser->type('motivo', 'Necesito revisar el puntaje de Dictado 1')
                ->click('form[action*="solicitar-desbloqueo"] button[type="submit"]')
                ->waitForText('Solicitud enviada', 15);

            $this->assertSame(
                1,
                SolicitudEdicionNota::where('estado', 'Pendiente')->count()
            );

            // ---------- 2. SUBDIRECTORA: rechaza ----------
            $this->logoutDe($browser);
            $this->loginComo($browser, self::EMAIL_SUBDIRECTOR)
                ->visit('/academico/notas/solicitudes')
                ->click('form[action*="rechazar"] button[type="submit"]')
                ->waitForText('Solicitud rechazada', 15);

            $this->assertSame(
                1,
                SolicitudEdicionNota::where('estado', 'Rechazada')->count()
            );

            // El parcial sigue bloqueado.
            $this->assertSame(
                1,
                CorteCerrado::where('aula_asignatura_docente_id', $asignacion->id)
                    ->where('corte_evaluativo_id', $corteId)->count()
            );
        });
    }
}
