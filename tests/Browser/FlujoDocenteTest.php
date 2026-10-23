<?php

namespace Tests\Browser;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Asignatura;
use App\Models\CorteEvaluativo;
use App\Models\Matricula;
use Laravel\Dusk\Browser;

/**
 * E2E 2 — FLUJO DEL DOCENTE GUÍA.
 *
 * Login → panel del tutor (mis alumnos, rendimiento) → toma de asistencia
 * de aula (marcando presentes y una ausencia) → evaluación de su asignatura
 * (Lengua y Literatura) → verificación en base de datos.
 */
class FlujoDocenteTest extends E2eTestCase
{
    public function test_docente_guia_ve_sus_alumnos_y_toma_asistencia(): void
    {
        $aula = Aula::where('nombre', '1ro A')->firstOrFail();
        $matriculas = Matricula::where('aula_id', $aula->id)->where('estado', 'activo')->orderBy('id')->get();
        $this->assertCount(3, $matriculas, 'El escenario E2E debe traer 3 matrículas activas.');

        $this->browse(function (Browser $browser) use ($matriculas) {
            // ---------- 1. Panel del Docente Guía ----------
            $this->loginComo($browser, self::EMAIL_DOCENTE_GUIA)
                ->visit('/academico/tutor/mis-alumnos')
                ->assertSee($matriculas[0]->alumno->nombre_completo)
                ->assertSee($matriculas[1]->alumno->nombre_completo)
                ->assertSee($matriculas[2]->alumno->nombre_completo);

            // ---------- 2. Tomar asistencia de aula (fecha fija para determinismo) ----------
            // Los radios son "sr-only": se marcan por JS (WebDriver no puede
            // hacer click físico sobre elementos con clip de 1px).
            $fecha = '2026-10-19';
            $browser->visit('/academico/asistencia/aula?fecha=' . $fecha);

            $this->marcarRadio($browser, "asistencias[0][estado_asistencia]", "Presente");
            $this->marcarRadio($browser, "asistencias[1][estado_asistencia]", "Presente");
            $this->marcarRadio($browser, "asistencias[2][estado_asistencia]", "Ausencia Injustificada");

            $browser->click('form[action*="asistencia"][method="POST"] button[type="submit"]')
                ->waitForText('Asistencia', 15);

            // Los radios reflejan el estado guardado al recargar la fecha.
            $browser->visit('/academico/asistencia/aula?fecha=' . $fecha)
                ->assertRadioSelected("asistencias[0][estado_asistencia]", "Presente")
                ->assertRadioSelected("asistencias[2][estado_asistencia]", "Ausencia Injustificada");
        });

        foreach ($matriculas as $i => $matricula) {
            $this->assertDatabaseHas('asistencia_aula', [
                'matricula_id' => $matricula->id,
                'estado_asistencia' => $i === 2 ? 'Ausencia Injustificada' : 'Presente',
            ]);
        }
    }

    public function test_docente_guia_evalua_su_asignatura_y_consulta_rendimiento(): void
    {
        $aula = Aula::where('nombre', '1ro A')->firstOrFail();
        $lenguaId = Asignatura::where('nombre', 'Lengua y Literatura')->value('id');
        $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)
            ->where('asignatura_id', $lenguaId)->firstOrFail();
        $corteId = CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)
            ->where('numero', 1)->value('id');
        $matriculas = Matricula::where('aula_id', $aula->id)->where('estado', 'activo')->orderBy('id')->get();

        $this->browse(function (Browser $browser) use ($asignacion, $corteId, $matriculas) {
            // ---------- 1. Crear actividades: acumulado + examen ----------
            $this->loginComo($browser, self::EMAIL_DOCENTE_GUIA)
                ->visit("/academico/notas/actividades/{$asignacion->id}?corte_id={$corteId}")
                ->assertPathIs("/academico/notas/actividades/{$asignacion->id}")
                ->type('nombre', 'Dictado 1')
                ->select('tipo', 'acumulado')
                ->type('puntaje_maximo', '20')
                ->click('form[action*="actividades"] button[type="submit"]')
                ->waitForText('Actividad guardada correctamente', 15)
                ->type('nombre', 'Examen Corte 1')
                ->select('tipo', 'examen')
                ->type('puntaje_maximo', '40')
                ->click('form[action*="actividades"] button[type="submit"]')
                ->waitForText('Actividad guardada correctamente', 15);

            $this->assertDatabaseHas('actividad_evaluativa', [
                'aula_asignatura_docente_id' => $asignacion->id,
                'nombre' => 'Dictado 1',
                'puntaje_maximo' => 20,
            ]);
            $this->assertDatabaseHas('actividad_evaluativa', [
                'aula_asignatura_docente_id' => $asignacion->id,
                'nombre' => 'Examen Corte 1',
                'puntaje_maximo' => 40,
            ]);

            // ---------- 2. Evaluar: acumulado 20/14/8 + examen 40/30/20 ----------
            $dictadoId = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('nombre', 'Dictado 1')->value('id');
            $examenId = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('nombre', 'Examen Corte 1')->value('id');
            $puntajesDictado = [20, 14, 8];
            $puntajesExamen = [40, 30, 20];

            $browser->visit("/academico/notas/evaluar/{$asignacion->id}?corte_evaluativo_id={$corteId}")
                ->assertSee($matriculas[0]->alumno->nombre_completo);

            foreach ($matriculas as $i => $matricula) {
                $browser->type("notas[{$matricula->id}][{$dictadoId}]", (string) $puntajesDictado[$i])
                    ->type("notas[{$matricula->id}][{$examenId}]", (string) $puntajesExamen[$i]);
            }

            $browser->click('#form-calificaciones button[type="submit"]')
                ->waitForText('Calificaciones actualizadas', 15);

            // Escalado MINED: NF = suma contra el total oficial del corte (100).
            // 20+40 = 60 => AE (umbral de aprobado); 14+30 = 44 => AI; 8+20 = 28 => AI.
            $this->assertDatabaseHas('nota', [
                'matricula_id' => $matriculas[0]->id,
                'aula_asignatura_docente_id' => $asignacion->id,
                'corte_evaluativo_id' => $corteId,
            ]);

            $notaAprobada = \App\Models\Nota::where('matricula_id', $matriculas[0]->id)
                ->where('corte_evaluativo_id', $corteId)->first();
            $this->assertEqualsWithDelta(60, (float) $notaAprobada->nota_cuantitativa, 0.01);
            $this->assertSame('AE', $notaAprobada->indicadorLogro->codigo);

            $notaBaja = \App\Models\Nota::where('matricula_id', $matriculas[2]->id)
                ->where('corte_evaluativo_id', $corteId)->first();
            $this->assertEqualsWithDelta(28, (float) $notaBaja->nota_cuantitativa, 0.01);
            $this->assertSame('AI', $notaBaja->indicadorLogro->codigo);

            // ---------- 3. Rendimiento del grupo ----------
            $browser->visit('/academico/tutor/rendimiento')
                ->assertPathIs('/academico/tutor/rendimiento');
        });
    }
}
