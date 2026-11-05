<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\AnioEscolar;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test de diagnóstico: verifica por qué Subdirector y Docente Guía
 * no pueden ver los detalles (show) de un aula.
 *
 * Ejecutar con:
 *   php artisan test --filter=AulaShowDiagnosticoTest
 */
class AulaShowDiagnosticoTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $subdirector;
    protected Usuario $docenteGuia;
    protected Usuario $director;
    protected Aula $aulaConGuia;
    protected Aula $aulaSinGuia;

    protected function setUp(): void
    {
        parent::setUp();

        // Siembra permisos y roles
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\PermisoSeeder']);

        // Usuarios con roles
        $this->director = Usuario::factory()->create();
        $this->director->assignRole('Director');

        $this->subdirector = Usuario::factory()->create();
        $this->subdirector->assignRole('Subdirector');

        $this->docenteGuia = Usuario::factory()->create();
        $this->docenteGuia->assignRole('Docente Guia');

        // Docentes vinculados a usuarios
        $docenteSubdirector = Docente::factory()->create(['usuario_id' => $this->subdirector->id]);
        $docenteGuiaModel = Docente::factory()->create(['usuario_id' => $this->docenteGuia->id]);
        $docenteOtro = Docente::factory()->create(); // Docente guía de otra aula

        $modalidad = Modalidad::factory()->create(['nombre' => 'Primaria Regular']);
        $grado = Grado::factory()->create(['nombre' => '1ro', 'modalidad_id' => $modalidad->id]);
        $anio = AnioEscolar::factory()->create(['nombre' => '2026', 'activo' => true]);

        // Aula cuya guía es el Docente Guía del test (la puede ver)
        $this->aulaConGuia = Aula::factory()->create([
            'modalidad_id' => $modalidad->id,
            'grado_id' => $grado->id,
            'anio_escolar_id' => $anio->id,
            'docente_guia_id' => $docenteGuiaModel->id,
        ]);

        // Aula cuya guía es OTRO docente (el Docente Guía del test NO debe verla)
        $this->aulaSinGuia = Aula::factory()->create([
            'modalidad_id' => $modalidad->id,
            'grado_id' => $grado->id,
            'anio_escolar_id' => $anio->id,
            'docente_guia_id' => $docenteOtro->id,
        ]);
    }

    /**
     * Subdirector debe poder ver cualquier aula (before() de AulaPolicy)
     */
    public function test_subdirector_puede_ver_show_de_cualquier_aula(): void
    {
        foreach ([$this->aulaConGuia, $this->aulaSinGuia] as $aula) {
            $response = $this->actingAs($this->subdirector)
                ->get(route('academico.aulas.show', $aula));

            echo "\n=== SUBDIRECTOR -> show(aula #{$aula->id}) ===\n";
            echo "Status: " . $response->getStatusCode() . "\n";
            if ($response->getStatusCode() !== 200) {
                echo "Contenido: " . $response->getContent() . "\n";
            }

            $response->assertOk();
        }
    }

    /**
     * Director debe poder ver cualquier aula
     */
    public function test_director_puede_ver_show_de_cualquier_aula(): void
    {
        $response = $this->actingAs($this->director)
            ->get(route('academico.aulas.show', $this->aulaConGuia));

        echo "\n=== DIRECTOR -> show(aula #{$this->aulaConGuia->id}) ===\n";
        echo "Status: " . $response->getStatusCode() . "\n";

        $response->assertOk();
    }

    /**
     * Docente Guía SOLO debe ver el aula de la cual es titular.
     * Si tiene registro de Docente vinculado, debe ver su propia aula.
     */
    public function test_docente_guia_puede_ver_su_propia_aula(): void
    {
        $response = $this->actingAs($this->docenteGuia)
            ->get(route('academico.aulas.show', $this->aulaConGuia));

        echo "\n=== DOCENTE GUÍA -> show(aula propia #{$this->aulaConGuia->id}) ===\n";
        echo "Status: " . $response->getStatusCode() . "\n";
        if ($response->getStatusCode() !== 200) {
            echo "Contenido: " . $response->getContent() . "\n";
        }

        $response->assertOk();
    }

    /**
     * Docente Guía NO debe ver el aula de otro docente guía (abort 403)
     */
    public function test_docente_guia_no_puede_ver_aula_de_otro(): void
    {
        $response = $this->actingAs($this->docenteGuia)
            ->get(route('academico.aulas.show', $this->aulaSinGuia));

        echo "\n=== DOCENTE GUÍA -> show(aula AJENA #{$this->aulaSinGuia->id}) ===\n";
        echo "Status: " . $response->getStatusCode() . "\n";

        $response->assertForbidden();
    }

    /**
     * Diagnóstico: verifica qué permisos y roles tiene cada usuario
     */
    public function test_diagnostico_permisos_usuarios(): void
    {
        $usuarios = [
            'Director' => $this->director,
            'Subdirector' => $this->subdirector,
            'Docente Guia' => $this->docenteGuia,
        ];

        foreach ($usuarios as $nombre => $usuario) {
            echo "\n=== USUARIO: {$nombre} (ID: {$usuario->id}) ===\n";
            echo "Roles: " . implode(', ', $usuario->getRoleNames()->toArray()) . "\n";
            echo "Permisos: " . implode(', ', $usuario->getAllPermissions()->pluck('name')->toArray()) . "\n";
            echo "Tiene aulas.ver: " . ($usuario->hasPermissionTo('aulas.ver') ? 'SI' : 'NO') . "\n";
            echo "Tiene aulas.gestionar: " . ($usuario->hasPermissionTo('aulas.gestionar') ? 'SI' : 'NO') . "\n";

            $docente = Docente::where('usuario_id', $usuario->id)->first();
            echo "Registro Docente vinculado: " . ($docente ? 'SI (id=' . $docente->id . ')' : 'NO') . "\n";
        }
    }
}
