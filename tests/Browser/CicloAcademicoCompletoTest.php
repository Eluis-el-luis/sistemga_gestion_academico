<?php

namespace Tests\Browser;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;

/**
 * E2E 1 — CICLO ACADÉMICO COMPLETO (el flujo central del negocio).
 *
 * Director: aperturar aula (malla clonada automáticamente) → asignar docente
 * por asignatura (masivo) → registrar alumno → matricularlo.
 * Docente por Asignatura: crear actividad evaluativa → evaluar notas.
 * Director: verificar boletín con la nota registrada.
 */
class CicloAcademicoCompletoTest extends E2eTestCase
{
    public function test_director_apertura_aula_y_registra_alumno(): void
    {
        $anioId = AnioEscolar::where('nombre', '2026')->value('id');
        $primariaId = Modalidad::where('nombre', 'Primaria Regular')->value('id');
        $grado1roId = Grado::where('nombre', '1ro')->where('modalidad_id', $primariaId)->value('id');
        $anaId = Docente::whereHas('usuario', fn ($q) => $q->where('email', self::EMAIL_DOCENTE_MATERIA))->value('id');

        $this->browse(function (Browser $browser) use ($anioId, $primariaId, $grado1roId, $anaId) {
            // ---------- 1. DIRECTOR: aperturar nueva aula "1ro B" ----------
            $this->loginComo($browser, self::EMAIL_DIRECTOR)
                ->visit('/academico/aulas/create')
                ->assertSee('Aperturar Nueva Aula')
                ->select('anio_escolar_id', (string) $anioId)
                ->select('modalidad_id', (string) $primariaId)
                ->select('grado_id', (string) $grado1roId)
                ->type('nombre', 'B')
                ->select('docente_guia_id', (string) $anaId)
                ->click('form[action*="aulas"] button[type="submit"]')
                ->waitForLocation('/academico/aulas', 15)
                ->assertSee('1ro');

            $aula = Aula::where('nombre', 'B')->where('anio_escolar_id', $anioId)->firstOrFail();
            $this->assertNotNull($aula, 'El aula 1ro B debe existir tras la apertura.');

            // La malla de 1ro (11 asignaturas) se clonó automáticamente al aula.
            $this->assertSame(11, AulaAsignaturaDocente::where('aula_id', $aula->id)->count());

            // ---------- 2. DIRECTOR: asignar docente vía asignación masiva ----------
            $matematicaId = Asignatura::where('nombre', 'Matemática')->value('id');

            $browser->visit('/academico/asignaciones')
                ->assertSee('1ro')
                ->select('asignatura_id', (string) $matematicaId)
                ->select('docente_id', (string) $anaId)
                ->check('input[name="aulas_ids[]"][value="' . $aula->id . '"]')
                ->click('form[action*="masivo"] button[type="submit"]')
                ->waitForText('Matemática', 15);

            $this->assertDatabaseHas('aula_asignatura_docente', [
                'aula_id' => $aula->id,
                'asignatura_id' => $matematicaId,
                'docente_id' => $anaId,
            ]);

            // ---------- 3. DIRECTOR: registrar nuevo alumno ----------
            $browser->visit('/academico/alumnos/create')
                ->assertSee('Registrar Nuevo Alumno')
                ->type('codigo_unico_persona', 'E2EA000010')
                ->type('nombre_completo', 'Lucía Ramírez Solís')
                ->select('sexo', 'F')
                ->type('fecha_nacimiento', '2019-05-10')
                ->type('direccion_domiciliar', 'Col. E2E Sur, Managua')
                ->click('form[action*="alumnos"] button[type="submit"]')
                ->waitForLocation('/academico/alumnos', 15);

            // El index por defecto lista activos; filtramos por nombre para verificar la ficha.
            $browser->visit('/academico/alumnos?buscar=Ram')
                ->assertSee('Ramírez');

            $this->assertDatabaseHas('alumno', ['codigo_unico_persona' => 'E2EA000010']);

            // ---------- 4. DIRECTOR: matricular al alumno en el aula "1ro B" ----------
            $alumnoId = \App\Models\Alumno::where('codigo_unico_persona', 'E2EA000010')->value('id');

            $browser->visit('/academico/matriculas/create')
                ->assertSee('Matrícula')
                ->select('alumno_id', (string) $alumnoId)
                ->select('aula_id', (string) $aula->id)
                ->select('estado', 'activo')
                ->click('form[action*="matriculas"] button[type="submit"]')
                ->waitForLocation('/academico/matriculas', 15);

            $this->assertDatabaseHas('matricula', [
                'alumno_id' => $alumnoId,
                'aula_id' => $aula->id,
                'estado' => 'activo',
            ]);
        });
    }

    public function test_docente_crea_actividad_evalua_y_cerrar_parcial(): void
    {
        $aula = Aula::where('nombre', '1ro A')->firstOrFail();
        $matematicaId = Asignatura::where('nombre', 'Matemática')->value('id');
        $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)
            ->where('asignatura_id', $matematicaId)->firstOrFail();
        $corteId = \App\Models\CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)
            ->where('numero', 1)->value('id');
        $matriculas = Matricula::where('aula_id', $aula->id)->where('estado', 'activo')->orderBy('id')->get();

        $this->browse(function (Browser $browser) use ($asignacion, $corteId, $matriculas, $aula) {
            // ---------- 1. DOCENTE DE MATERIA: crear actividad evaluativa ----------
            $this->loginComo($browser, self::EMAIL_DOCENTE_MATERIA)
                ->visit("/academico/notas/actividades/{$asignacion->id}?corte_id={$corteId}")
                ->assertPathIs("/academico/notas/actividades/{$asignacion->id}")
                ->type('nombre', 'Tarea 1 - Sumas')
                ->select('tipo', 'acumulado')
                ->type('puntaje_maximo', '50')
                ->click('form[action*="actividades"] button[type="submit"]')
                ->waitForText('Actividad guardada correctamente', 15);

            $this->assertDatabaseHas('actividad_evaluativa', [
                'aula_asignatura_docente_id' => $asignacion->id,
                'nombre' => 'Tarea 1 - Sumas',
                'puntaje_maximo' => 50,
            ]);

            // ---------- 2. DOCENTE: evaluar los 3 alumnos en la planilla ----------
            $actividadId = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('nombre', 'Tarea 1 - Sumas')->value('id');

            $browser->visit("/academico/notas/evaluar/{$asignacion->id}?corte_evaluativo_id={$corteId}")
                ->assertSee($matriculas[0]->alumno->nombre_completo);

            foreach ($matriculas as $i => $matricula) {
                $browser->type("notas[{$matricula->id}][{$actividadId}]", (string) (45 - $i * 10));
            }

            $browser->click('#form-calificaciones button[type="submit"]')
                ->waitForText('Calificaciones actualizadas', 15);

            // Escalado MINED: NF = suma contra el total oficial del corte (100).
            // 45/100 => 45 (AI). Las actividades no configuradas valen 0.
            $this->assertDatabaseHas('nota', [
                'aula_asignatura_docente_id' => $asignacion->id,
                'corte_evaluativo_id' => $corteId,
            ]);

            // Nota final escalada: 45 puntos acumulados => NF 45 (AI).
            $nota = \App\Models\Nota::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('corte_evaluativo_id', $corteId)
                ->orderBy('matricula_id')->first();
            $this->assertNotNull($nota, 'La nota consolidada debe existir.');
            $this->assertEqualsWithDelta(45, (float) $nota->nota_cuantitativa, 0.01);
            $this->assertSame('AI', $nota->indicadorLogro->codigo);

            // ---------- 3. DOCENTE: cerrar el parcial (modal de confirmación) ----------
            $browser->visit("/academico/notas/planilla/{$asignacion->id}?corte_evaluativo_id={$corteId}");

            $this->abrirModal($browser, 'abrir-modal-cierre');

            $browser->click('form[action*="cerrar"] button[type="submit"]')
                ->waitForText('Calificaciones cerradas', 15);

            $this->assertDatabaseHas('corte_cerrado', [
                'aula_asignatura_docente_id' => $asignacion->id,
                'corte_evaluativo_id' => $corteId,
                'bloqueado' => true,
            ]);
        });
    }

    public function test_director_verifica_boletin_con_notas(): void
    {
        $aula = Aula::where('nombre', '1ro A')->firstOrFail();
        $matricula = Matricula::where('aula_id', $aula->id)->where('estado', 'activo')->firstOrFail();
        $alumnoNombre = $matricula->alumno->nombre_completo;

        $this->browse(function (Browser $browser) use ($aula, $matricula, $alumnoNombre) {
            $this->loginComo($browser, self::EMAIL_DIRECTOR)
                ->visit('/academico/boletines?aula_id=' . $aula->id)
                ->assertSee($alumnoNombre)
                ->click('a[href*="boletines/' . $matricula->id . '"]')
                // El certificado muestra el nombre en mayúsculas.
                ->assertSee(mb_strtoupper($alumnoNombre));
        });
    }
}
