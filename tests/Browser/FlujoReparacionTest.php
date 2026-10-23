<?php

namespace Tests\Browser;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Asignatura;
use App\Models\CorteCerrado;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\ExamenReparacion;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Usuario;
use Laravel\Dusk\Browser;

/**
 * E2E 4 — FLUJO DE EXAMEN DE REPARACIÓN (recuperación académica).
 *
 * Contexto: alumno de SECUNDARIA (grado 7mo: los grados 1-5 de primaria son
 * auto-promovidos y no reparan) con nota final reprobada y parcial cerrado.
 * Dirección: registra el examen de reparación por la UI → queda 'aprobado'
 * en base de datos → el certificado/boletín en PDF se genera correctamente.
 */
class FlujoReparacionTest extends E2eTestCase
{
    public function test_direccion_registra_examen_reparacion_y_genera_pdf(): void
    {
        $anio = AnioEscolar::where('nombre', '2026')->firstOrFail();
        $secundaria = Modalidad::where('nombre', 'Secundaria Regular')->firstOrFail();
        $grado7mo = Grado::where('nombre', '7mo')->where('modalidad_id', $secundaria->id)->firstOrFail();
        $matematica = Asignatura::where('nombre', 'Matemática')->firstOrFail();

        $docenteGuia = Docente::whereHas('usuario', fn ($q) => $q->where('email', self::EMAIL_DOCENTE_GUIA))->firstOrFail();
        $docenteAna = Docente::whereHas('usuario', fn ($q) => $q->where('email', self::EMAIL_DOCENTE_MATERIA))->firstOrFail();

        // ---------------- Arrange: aula de 7mo con un reprobado ----------------
        $aula = Aula::firstOrCreate(
            ['nombre' => '7mo A', 'anio_escolar_id' => $anio->id],
            [
                'grado_id' => $grado7mo->id,
                'modalidad_id' => $secundaria->id,
                'turno' => 'Matutino',
                'docente_guia_id' => $docenteGuia->id,
                'cupo' => 30,
            ]
        );

        $asignacion = AulaAsignaturaDocente::firstOrCreate(
            ['aula_id' => $aula->id, 'asignatura_id' => $matematica->id, 'anio_escolar_id' => $anio->id],
            ['docente_id' => $docenteAna->id, 'horas_semanales' => 5]
        );

        $alumno = Alumno::firstOrCreate(
            ['codigo_unico_persona' => 'E2EA000020'],
            [
                'nombre_completo' => 'Diego Ruiz Mairena',
                'sexo' => 'M',
                'fecha_nacimiento' => '2012-08-20',
                'direccion_domiciliar' => 'Barrio E2E Norte, Managua',
                'hermanos_en_colegio' => false,
                'acepta_compromiso_cristiano' => true,
            ]
        );

        $matricula = Matricula::firstOrCreate(
            ['alumno_id' => $alumno->id, 'aula_id' => $aula->id, 'anio_escolar_id' => $anio->id],
            ['estado' => 'activo', 'fecha_matricula' => now()->toDateString()]
        );

        $corte = CorteEvaluativo::where('anio_escolar_id', $anio->id)->where('numero', 1)->firstOrFail();

        $actividad = \App\Models\ActividadEvaluativa::firstOrCreate(
            ['aula_asignatura_docente_id' => $asignacion->id, 'nombre' => 'Prueba Diagnostico'],
            [
                'corte_evaluativo_id' => $corte->id,
                'tipo' => 'acumulado',
                'puntaje_maximo' => 20,
                'fecha' => now()->toDateString(),
            ]
        );

        \App\Models\NotaActividad::firstOrCreate(
            ['matricula_id' => $matricula->id, 'actividad_evaluativa_id' => $actividad->id],
            ['nota_obtenida' => 8] // 8/20 => 40 => AI (reprobado)
        );

        // 8/20 puntos: escalar contra el total oficial del corte (60+40=100) => 40 (AI, reprobado).
        app(\App\Services\NotaService::class)->registrarNotaFinal(
            $matricula->id,
            $asignacion->id,
            $corte->id,
            8,
            100,
            null
        );

        CorteCerrado::firstOrCreate(
            ['aula_asignatura_docente_id' => $asignacion->id, 'corte_evaluativo_id' => $corte->id],
            ['bloqueado' => true]
        );

        // ---------------- Act: UI de Dirección ----------------
        $this->browse(function (Browser $browser) use ($alumno, $matricula) {
            $this->loginComo($browser, self::EMAIL_DIRECTOR)
                ->visit('/academico/reparacion')
                ->assertSee($alumno->nombre_completo);

            // El formulario está dentro de un acordeón Alpine por alumno: se
            // expande haciendo click en la cabecera (div con atributo @click).
            $browser->script(
                "const h = [...document.querySelectorAll('h3')].find(e => e.textContent.includes('" .
                addslashes($alumno->nombre_completo) .
                "')); let el = h; while (el && !el.hasAttribute('@click')) el = el.parentElement; el && el.click();"
            );

            $browser->type('nota_obtenida', '65')
                ->click('form[action*="reparacion"] button[type="submit"]')
                ->waitForText('Examen de reparación registrado', 15);

            $this->assertSame(
                1,
                ExamenReparacion::where('matricula_id', $matricula->id)
                    ->where('nota_obtenida', 65)->count()
            );
            $this->assertSame(
                'aprobado',
                ExamenReparacion::where('matricula_id', $matricula->id)->value('resultado')
            );
        });

        // ---------------- Assert: PDF del boletín genera correctamente ----------------
        $director = Usuario::where('email', self::EMAIL_DIRECTOR)->firstOrFail();

        $this->actingAs($director)
            ->get(route('academico.boletines.pdf.boletin', $matricula))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        // El examen aprobado queda reflejado en el historial del alumno.
        $this->assertSame(
            1,
            ExamenReparacion::where('matricula_id', $matricula->id)
                ->where('asignatura_id', $matematica->id)
                ->where('resultado', 'aprobado')->count()
        );
    }
}
