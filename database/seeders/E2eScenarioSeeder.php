<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Escenario de negocio E2E: cuentas por rol + aula con asignaciones y alumnos.
 *
 * Lo ejecuta DatabaseTruncation en CADA test (tras el truncate de tablas de
 * negocio). Es idempotente. Los catálogos solo se siembran si están vacíos.
 * NUNCA debe usarse contra la base de datos real.
 */
class E2eScenarioSeeder extends Seeder
{
    public const PASSWORD = 'password';

    /**
     * Hash bcrypt precalculado de 'password' (coste 10). Bcrypt valida con el
     * coste embebido en el hash, así que sirve aunque BCRYPT_ROUNDS sea otro.
     * Evita recalcular 8 hashes (bcrypt) en cada test.
     */
    public const PASSWORD_HASH = '$2y$10$Gru1qbrnxTvACYjcP2EDkuKf8MFu8avHmo1c3GWyu5HYdOIn4TsL6';

    public function run(): void
    {
        // Catálogos: solo si la base acaba de ser migrada (vacía).
        if (AnioEscolar::count() === 0) {
            $this->call(E2eSeeder::class);
        }

        $this->sembrarCuentas();
        $this->sembrarEscenarioAcademico();
    }

    private function sembrarCuentas(): void
    {
        $cuentas = [
            ['email' => 'director.e2e@ccn.test', 'nombre' => 'Roberto Director E2E', 'rol' => 'Director'],
            ['email' => 'subdirector.e2e@ccn.test', 'nombre' => 'Silvia Subdirectora E2E', 'rol' => 'Subdirector'],
            ['email' => 'gestor.e2e@ccn.test', 'nombre' => 'Gustavo Gestor E2E', 'rol' => 'Gestor de Usuarios'],
            ['email' => 'coordinador.e2e@ccn.test', 'nombre' => 'Carla Coordinadora E2E', 'rol' => 'Coordinador'],
            ['email' => 'secretaria.e2e@ccn.test', 'nombre' => 'Sofía Secretaria E2E', 'rol' => 'Secretaria'],
            ['email' => 'guia.e2e@ccn.test', 'nombre' => 'Carlos Guía E2E', 'rol' => 'Docente Guia'],
            ['email' => 'materia.e2e@ccn.test', 'nombre' => 'Ana Materia E2E', 'rol' => 'Docente por Asignatura'],
            ['email' => 'alumno.e2e@ccn.test', 'nombre' => 'Andrés Alumno E2E', 'rol' => 'Alumno'],
        ];

        foreach ($cuentas as $cuenta) {
            $usuario = Usuario::firstOrCreate(
                ['email' => $cuenta['email']],
                [
                    'nombre_completo' => $cuenta['nombre'],
                    'password' => self::PASSWORD_HASH,
                    'activo' => true,
                    'email_verified_at' => now(),
                ]
            );
            $usuario->syncRoles([$cuenta['rol']]);
        }

        Usuario::where('email', 'martha.salinas@ccn.edu.ni')->update(['password' => self::PASSWORD_HASH]);
    }

    private function sembrarEscenarioAcademico(): void
    {
        // Personal docente
        $usuarioGuia = Usuario::where('email', 'guia.e2e@ccn.test')->firstOrFail();
        $docenteGuia = Docente::firstOrCreate(
            ['usuario_id' => $usuarioGuia->id],
            [
                'codigo_unico_persona' => 'E2E0000001',
                'sexo' => 'M',
                'es_coordinador' => false,
                'modalidad_coordina_id' => null,
            ]
        );

        $usuarioMateria = Usuario::where('email', 'materia.e2e@ccn.test')->firstOrFail();
        $docenteMateria = Docente::firstOrCreate(
            ['usuario_id' => $usuarioMateria->id],
            [
                'codigo_unico_persona' => 'E2E0000002',
                'sexo' => 'F',
                'es_coordinador' => false,
                'modalidad_coordina_id' => null,
            ]
        );

        // Estructura: aula "1ro A" (Primaria, año 2026)
        $anio = AnioEscolar::where('nombre', '2026')->firstOrFail();
        $primaria = Modalidad::where('nombre', 'Primaria Regular')->firstOrFail();
        $grado1ro = Grado::where('nombre', '1ro')->where('modalidad_id', $primaria->id)->firstOrFail();

        $aula = Aula::firstOrCreate(
            ['nombre' => '1ro A', 'anio_escolar_id' => $anio->id],
            [
                'grado_id' => $grado1ro->id,
                'modalidad_id' => $primaria->id,
                'turno' => 'Matutino',
                'docente_guia_id' => $docenteGuia->id,
                'cupo' => 30,
            ]
        );

        // Asignaciones de asignaturas a docentes dentro del aula
        $matematica = Asignatura::where('nombre', 'Matemática')->firstOrFail();
        $lengua = Asignatura::where('nombre', 'Lengua y Literatura')->firstOrFail();

        AulaAsignaturaDocente::firstOrCreate(
            [
                'aula_id' => $aula->id,
                'asignatura_id' => $matematica->id,
                'anio_escolar_id' => $anio->id,
            ],
            ['docente_id' => $docenteMateria->id, 'horas_semanales' => 5]
        );

        AulaAsignaturaDocente::firstOrCreate(
            [
                'aula_id' => $aula->id,
                'asignatura_id' => $lengua->id,
                'anio_escolar_id' => $anio->id,
            ],
            ['docente_id' => $docenteGuia->id, 'horas_semanales' => 5]
        );

        // Alumnos matriculados (3 activos en "1ro A")
        $alumnos = [
            ['nombre' => 'Juan Pérez Gómez', 'sexo' => 'M', 'codigo' => 'E2EA000001'],
            ['nombre' => 'María López Rivas', 'sexo' => 'F', 'codigo' => 'E2EA000002'],
            ['nombre' => 'Pedro Sánchez Cruz', 'sexo' => 'M', 'codigo' => 'E2EA000003'],
        ];

        foreach ($alumnos as $datos) {
            $alumno = Alumno::firstOrCreate(
                ['codigo_unico_persona' => $datos['codigo']],
                [
                    'nombre_completo' => $datos['nombre'],
                    'sexo' => $datos['sexo'],
                    'fecha_nacimiento' => '2019-03-15',
                    'direccion_domiciliar' => 'Barrio E2E, Managua',
                    'hermanos_en_colegio' => false,
                    'acepta_compromiso_cristiano' => true,
                ]
            );

            Matricula::firstOrCreate(
                [
                    'alumno_id' => $alumno->id,
                    'aula_id' => $aula->id,
                    'anio_escolar_id' => $anio->id,
                ],
                [
                    'estado' => 'activo',
                    'fecha_matricula' => now()->toDateString(),
                ]
            );
        }
    }
}
