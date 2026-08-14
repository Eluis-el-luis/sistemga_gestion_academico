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

        // 3. Obtener o crear los roles en el ecosistema de Spatie
        $director = Role::firstOrCreate(['name' => 'Director']);
        $subdirector = Role::firstOrCreate(['name' => 'Subdirector']);
        $docente = Role::firstOrCreate(['name' => 'Docente']);
        
        $alumno = Role::firstOrCreate(['name' => 'Alumno']);

        // 4. Asignar permisos al Director (Según matriz: Gestiona casi todo, ve el resto)
        $director->syncPermissions([
            'alumnos.gestionar', 'notas.ver', 'asistencia.ver', 'indicadores.ver', 
            'boletines.ver', 'malla.gestionar', 'asignaturas_aula.gestionar', 
            'horarios.ver', 'avance.ver', 'apoyo_padres.ver', 'reparacion.ver', 
            'reportes.gestionar', 'configuracion.gestionar'
        ]);

        // 5. Asignar permisos al Subdirector (Según matriz: Similar al director, pero supervisa más)
        $subdirector->syncPermissions([
            'alumnos.supervisar', 'notas.ver', 'asistencia.ver', 'indicadores.ver', 
            'boletines.ver', 'malla.gestionar', 'asignaturas_aula.gestionar', 
            'horarios.ver', 'avance.ver', 'apoyo_padres.ver', 'reparacion.ver', 
            'reportes.supervisar', 'configuracion.ver'
        ]);

        // 6. Asignar permisos al Docente (Según matriz: Gestiona sus áreas, ve el resto)
        // Nota: El alcance de *qué* alumnos gestiona se controlará en las Policies, 
        // aquí solo le damos el permiso general para acceder al módulo.
        $docente->syncPermissions([
            'alumnos.gestionar', 'alumnos.ver', 'notas.gestionar', 'notas.ver', 
            'asistencia.gestionar', 'asistencia.ver', 'indicadores.gestionar', 
            'indicadores.ver', 'boletines.gestionar', 'asignaturas_aula.gestionar', 
            'asignaturas_aula.ver', 'horarios.gestionar', 'horarios.ver', 
            'avance.gestionar', 'avance.ver', 'apoyo_padres.gestionar', 
            'reparacion.gestionar', 'reparacion.ver', 'reportes.ver'
        ]);
    }
}