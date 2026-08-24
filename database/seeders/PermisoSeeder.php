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
            // Aulas (¡AÑADIDOS AQUÍ!)
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
            // Reportes
            'reportes.gestionar', 'reportes.ver', 'reportes.supervisar',
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
        $docente_guia = Role::firstOrCreate(['name' => 'Docente Guía', 'guard_name' => 'web']);
        $docente_asignatura = Role::firstOrCreate(['name' => 'Docente por Asignatura', 'guard_name' => 'web']);
        $alumno = Role::firstOrCreate(['name' => 'Alumno', 'guard_name' => 'web']);
        $coordinador = Role::firstOrCreate(['name' => 'Coordinador', 'guard_name' => 'web']);
        $gestor = Role::firstOrCreate(['name' => 'Gestor de Usuarios', 'guard_name' => 'web']);

        // 4. Asignar permisos al Director 
        $director->syncPermissions([
            'alumnos.gestionar','alumnos.ver', 'notas.ver', 'asistencia.ver', 'indicadores.ver', 
            'boletines.ver', 'malla.gestionar', 'aulas.gestionar', 'aulas.ver', 'asignaturas_aula.gestionar', 
            'horarios.ver', 'horarios.gestionar', 'avance.ver', 'apoyo_padres.ver', 'reparacion.ver', 
            'reportes.gestionar', 'configuracion.gestionar', 'configuracion.ver'
        ]);

        // 5. Asignar permisos al Subdirector
        $subdirector->syncPermissions([
            'alumnos.supervisar','alumnos.ver', 'notas.ver', 'asistencia.ver', 'indicadores.ver', 
            'boletines.ver', 'malla.gestionar', 'aulas.gestionar', 'aulas.ver', 'asignaturas_aula.gestionar', 
            'horarios.ver', 'horarios.gestionar', 'avance.ver', 'apoyo_padres.ver', 'reparacion.ver', 
            'reportes.supervisar', 'configuracion.ver'
        ]);

        // 6. EL DOCENTE GUÍA
        $docente_guia->syncPermissions([
            'alumnos.gestionar', 'alumnos.ver', 
            'aulas.ver', // Permite que vean su aula en el sistema
            'notas.gestionar', 'notas.ver', 
            'asistencia.gestionar', 'asistencia.ver', 'indicadores.gestionar', 
            'indicadores.ver', 'boletines.gestionar', 'asignaturas_aula.gestionar', 
            'asignaturas_aula.ver', 'horarios.gestionar', 'horarios.ver', 
            'avance.gestionar', 'avance.ver', 'apoyo_padres.gestionar', 
            'reparacion.gestionar', 'reparacion.ver', 'reportes.ver'
        ]);

        // 7. EL DOCENTE POR ASIGNATURA
        $docente_asignatura->syncPermissions([
            'alumnos.ver', 
            'aulas.ver', // Permite que vean los horarios/aulas donde dan clases
            'notas.gestionar', 'notas.ver', 
            'asistencia.gestionar', 'asistencia.ver', 'indicadores.gestionar', 
            'indicadores.ver', 'asignaturas_aula.ver', 'horarios.ver', 
            'avance.gestionar', 'avance.ver', 'reparacion.gestionar', 'reparacion.ver'
        ]);

        // 8. EL GESTOR DE USUARIOS (Nuevo)
        // Solo puede gestionar usuarios, alumnos (matrícula inicial) y estructurar aulas/mallas.
        $gestor->syncPermissions([
            'configuracion.gestionar', 'configuracion.ver', 
            'alumnos.gestionar', 'alumnos.ver',
            'aulas.gestionar', 'aulas.ver',
            'malla.gestionar', 'malla.ver',
            'asignaturas_aula.gestionar', 'asignaturas_aula.ver'
        ]);

        // 9. EL COORDINADOR (Nuevo)
        // Solo permisos de "ver" y reportes para poder supervisar a sus docentes.
        $coordinador->syncPermissions([
            'alumnos.ver', 'aulas.ver', 'notas.ver', 'asistencia.ver', 
            'indicadores.ver', 'horarios.ver', 'reportes.ver'
        ]);
    }
}