<?php

/*
|--------------------------------------------------------------------------
| Configuración del Módulo de Respaldos
|--------------------------------------------------------------------------
|
| Parámetros del motor de respaldo de la base de datos. Creado tras el
| incidente de Oct 2026 donde se perdió el contenido de la base de datos
| de Supabase (ver Contexto.md, sección 15.6 y 17).
|
*/

return [

    /*
    | Directorio local donde se guardan los archivos .zip de respaldo.
    | Está fuera del control de versiones (storage/app/.gitignore).
    */
    'directorio' => env('BACKUP_DIRECTORIO', storage_path('app/respaldos')),

    /*
    | Cantidad máxima de respaldos conservados. Al crear uno nuevo, se
    | eliminan los más antiguos que excedan este número.
    */
    'retencion' => (int) env('BACKUP_RETENCION', 20),

    /*
    | Hora del respaldo automático diario (schedule:work).
    */
    'hora_automatica' => env('BACKUP_HORA', '02:00'),

    /*
    | Cantidad de filas agrupadas por cada sentencia INSERT (multi-row).
    | Valores altos = archivo más compacto y restauración más rápida.
    */
    'filas_por_insert' => (int) env('BACKUP_FILAS_POR_INSERT', 200),

    /*
    | Tablas excluidas del respaldo, separadas por coma. Vacío = respaldar
    | TODO (incluye migrations, sesiones, caché y tablas de Spatie) para
    | que la restauración reproduzca el estado exacto de la base.
    */
    'excluir_tablas' => array_values(array_filter(array_map('trim', explode(',', (string) env('BACKUP_EXCLUIR', ''))))),

];
