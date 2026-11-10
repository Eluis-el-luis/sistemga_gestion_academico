<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Limpiar la caché de Spatie ANTES de crear o asignar permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // 1. Definir la lista completa de permisos basados en los módulos de la matriz
        $permisos = [
            // Alumnos y Matrícula
            'alumnos.gestionar', 'alumnos.ver', 'alumnos.supervisar',
            // Aulas
            'aulas.gestionar', 'aulas.ver',
            // Calificaciones (Notas)
            'notas.gestionar', 'notas.ver',
            // Asistencia
            'asistencia.gestionar', 'asistencia.ver',
            // Indicadores de Logro
            'indicadores.gestionar', 'indicadores.ver',
            // Boletines
            'boletines.gestionar', 'boletines.ver',
            // Malla Curricular
            'malla.gestionar', 'malla.ver',
            // Asignaturas por Aula
            'asignaturas_aula.gestionar', 'asignaturas_aula.ver',
            // Horarios
            'horarios.gestionar', 'horarios.ver',
            // Avance de Contenidos
            'avance.gestionar', 'avance.ver',
            // Apoyo de Padres
            'apoyo_padres.gestionar', 'apoyo_padres.ver',
            // Examen de Reparación
            'reparacion.gestionar', 'reparacion.ver',
            // Reportes - GRANULARES POR TIPO (Zero Trust)
            'reportes.gestionar', 'reportes.ver', 'reportes.supervisar',
            'reportes.control_notas.ver', 'reportes.control_notas.gestionar',
            'reportes.notas_globales.ver',
            'reportes.notas_pendientes.ver',
            'reportes.asistencia_global.ver',
            'reportes.estadisticas_asistencia.ver',
            'reportes.asistencia_seccion.ver',
            'reportes.asistencia_seccion_rango.ver',
            'reportes.asistencia_estudiante.ver',
            'reportes.rendimiento_corte.ver',
            'reportes.historial_estudiante.ver',
            'reportes.mined.ver',
            'reportes.mined_detallado.ver',
            'reportes.avance_programatico.ver',
            'reportes.estudiantes.ver',
            'reportes.padres.ver',
            'reportes.escalas.ver',
            'reportes.asignaturas_aprobadas.ver',
            // Reportes propios del docente (scope automático por aula)
            'reportes.mis_clases.ver',
            'reportes.mi_aula.ver',
            // Usuarios, Roles y Catálogos
            'configuracion.gestionar', 'configuracion.ver',
        ];

        // 2. Crear los permisos en la base de datos
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // 3. Obtener o crear los roles
        $director = Role::firstOrCreate(['name' => 'Director', 'guard_name' => 'web']);
        $subdirector = Role::firstOrCreate(['name' => 'Subdirector', 'guard_name' => 'web']);
        $docente_guia = Role::firstOrCreate(['name' => 'Docente Guia', 'guard_name' => 'web']);
        $docente_asignatura = Role::firstOrCreate(['name' => 'Docente por Asignatura', 'guard_name' => 'web']);
        $alumno = Role::firstOrCreate(['name' => 'Alumno', 'guard_name' => 'web']);
        $coordinador = Role::firstOrCreate(['name' => 'Coordinador', 'guard_name' => 'web']);
        $gestor = Role::firstOrCreate(['name' => 'Gestor de Usuarios', 'guard_name' => 'web']);
        $secretaria = Role::firstOrCreate(['name' => 'Secretaria', 'guard_name' => 'web']);

        // 4. Asignar permisos al Director (Poder absoluto)
        $director->syncPermissions($permisos);

        // 5. Asignar permisos al Subdirector (Supervisión total, sin gestión usuarios)
        $subdirector->syncPermissions([
            'alumnos.supervisar','alumnos.ver', 'notas.ver', 'asistencia.ver', 'asistencia.gestionar','indicadores.ver', 
            'boletines.ver', 'malla.gestionar', 'aulas.gestionar', 'aulas.ver', 'asignaturas_aula.gestionar', 
            'horarios.ver', 'horarios.gestionar', 'avance.ver', 'apoyo_padres.ver', 'reparacion.ver', 
            'reportes.supervisar', 'configuracion.gestionar', 'configuracion.ver',
            // Reportes granulares
            'reportes.control_notas.ver', 'reportes.control_notas.gestionar',
            'reportes.notas_globales.ver', 'reportes.notas_pendientes.ver',
            'reportes.asistencia_global.ver', 'reportes.estadisticas_asistencia.ver',
            'reportes.asistencia_seccion.ver', 'reportes.asistencia_seccion_rango.ver',
            'reportes.asistencia_estudiante.ver', 'reportes.rendimiento_corte.ver',
            'reportes.historial_estudiante.ver', 'reportes.mined.ver',
            'reportes.mined_detallado.ver', 'reportes.avance_programatico.ver',
            'reportes.estudiantes.ver', 'reportes.padres.ver', 'reportes.escalas.ver',
            'reportes.asignaturas_aprobadas.ver',
        ]);

        // 6. EL DOCENTE GUÍA - Reportes solo de SU aula (scope automático via middleware)
        $docente_guia->syncPermissions([
            'alumnos.gestionar', 'alumnos.ver', 
            'aulas.ver',
            'notas.gestionar', 'notas.ver', 
            'asistencia.gestionar', 'asistencia.ver', 'indicadores.gestionar', 
            'indicadores.ver', 'boletines.gestionar', 'asignaturas_aula.gestionar', 
            'asignaturas_aula.ver', 'horarios.gestionar', 'horarios.ver', 
            'avance.gestionar', 'avance.ver', 'apoyo_padres.gestionar', 
            'reparacion.gestionar', 'reparacion.ver',
            // Reportes propios (filtrados por aula en middleware)
            'reportes.mis_clases.ver', 'reportes.mi_aula.ver',
        ]);

        // 7. EL DOCENTE POR ASIGNATURA - Reportes solo de SUS asignaturas
        $docente_asignatura->syncPermissions([
            'alumnos.ver', 
            'aulas.ver',
            'notas.gestionar', 'notas.ver', 
            'asistencia.gestionar', 'asistencia.ver', 'indicadores.gestionar', 
            'indicadores.ver', 'asignaturas_aula.ver', 'horarios.ver', 
            'avance.gestionar', 'avance.ver', 'reparacion.gestionar', 'reparacion.ver',
            // Reportes propios (filtrados por asignatura en middleware)
            'reportes.mis_clases.ver',
        ]);

        // 8. EL GESTOR DE USUARIOS - Solo métricas operativas, NO académicas
        $gestor->syncPermissions([
            'configuracion.gestionar', 'configuracion.ver', 
            'alumnos.gestionar', 'alumnos.ver',
            'aulas.gestionar', 'aulas.ver',
            'malla.gestionar', 'malla.ver',
            'asignaturas_aula.gestionar', 'asignaturas_aula.ver',
            // Reportes operativos básicos
            'reportes.ver',
            'reportes.control_notas.ver', 'reportes.notas_globales.ver',
            'reportes.estudiantes.ver',
        ]);

        // 9. EL COORDINADOR - Solo SU modalidad (scope automático via middleware)
        $coordinador->syncPermissions([
            'alumnos.ver', 'aulas.ver', 'notas.ver', 'asistencia.ver', 
            'indicadores.ver', 'horarios.ver',
            // Reportes de SU modalidad (filtrados en middleware)
            'reportes.control_notas.ver', 'reportes.notas_globales.ver',
            'reportes.asistencia_global.ver', 'reportes.estadisticas_asistencia.ver',
            'reportes.asistencia_seccion.ver', 'reportes.rendimiento_corte.ver',
            'reportes.historial_estudiante.ver', 'reportes.mined_detallado.ver',
            'reportes.avance_programatico.ver', 'reportes.estudiantes.ver',
            'reportes.padres.ver', 'reportes.escalas.ver', 'reportes.asignaturas_aprobadas.ver',
        ]);

        // 10. LA SECRETARÍA - Reportes oficiales y operativos
        $secretaria->syncPermissions([
            'alumnos.gestionar', 'alumnos.ver',
            'notas.ver', 'asistencia.ver', 'boletines.ver',
            'reportes.ver', 'reportes.gestionar',
            'reportes.control_notas.ver', 'reportes.notas_globales.ver',
            'reportes.notas_pendientes.ver', 'reportes.asistencia_global.ver',
            'reportes.estadisticas_asistencia.ver', 'reportes.rendimiento_corte.ver',
            'reportes.historial_estudiante.ver', 'reportes.mined.ver',
            'reportes.mined_detallado.ver', 'reportes.estudiantes.ver',
            'reportes.padres.ver', 'reportes.escalas.ver',
            // Consulta operativa para atención de padres y administración diaria
            'aulas.ver', 'horarios.ver', 'malla.ver', 'asignaturas_aula.ver',
        ]);
    }
}