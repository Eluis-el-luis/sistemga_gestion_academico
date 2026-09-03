<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\MallaCurricularController;

Route::get('/', function () {
    return redirect()->route('login');
});

// --- RUTAS DEL PANEL PRINCIPAL Y TABLERO DE AVISOS ---
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Gestión de Avisos
Route::post('/dashboard/avisos', [\App\Http\Controllers\DashboardController::class, 'storeAviso'])
    ->middleware(['auth'])
    ->name('dashboard.avisos.store');

Route::put('/dashboard/avisos/{id}', [\App\Http\Controllers\DashboardController::class, 'updateAviso'])
    ->middleware(['auth'])
    ->name('dashboard.avisos.update');

Route::delete('/dashboard/avisos/{id}', [\App\Http\Controllers\DashboardController::class, 'destroyAviso'])
    ->middleware(['auth'])
    ->name('dashboard.avisos.destroy');
// -----------------------------------------------------

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {

    Route::post('/asistencia-personal/marcar', [\App\Http\Controllers\AsistenciaPersonalController::class, 'marcarLlegada'])->name('asistencia.personal.marcar');
    
    Route::prefix('academico')->name('academico.')->group(function () {

        // Catálogo de Asignaturas
        Route::resource('asignaturas', \App\Http\Controllers\AsignaturaController::class)->except(['create', 'show', 'edit']);
        
        Route::get('asistencia/personal', [\App\Http\Controllers\AsistenciaPersonalController::class, 'index'])->name('asistencia.personal.index');
        Route::resource('alumnos', AlumnoController::class);
        Route::resource('matriculas', MatriculaController::class);
        Route::patch('matriculas/{matricula}/retirar', [MatriculaController::class, 'retirar'])->name('matriculas.retirar');
        Route::patch('matriculas/{matricula}/reactivar', [MatriculaController::class, 'reactivar'])->name('matriculas.reactivar');
        // Ruta para actualizar el límite de horas del grado desde la malla
        Route::put('malla/grado/{grado}/horas', [\App\Http\Controllers\MallaCurricularController::class, 'actualizarHorasGrado'])->name('malla.grado.horas');

        // --- GESTIÓN DE AULAS Y SUS NUEVOS ACCESOS DIRECTOS ---
        Route::resource('aulas', AulaController::class);
        Route::get('asignaciones', [AulaController::class, 'indexAsignaciones'])->name('asignaciones.index');
        // NUEVA RUTA PARA LOS DETALLES DE ASIGNACIÓN:
        Route::get('asignaciones/{aula}', [AulaController::class, 'showAsignaciones'])->name('asignaciones.show');
        Route::get('gestor-horarios', [AulaController::class, 'indexHorarios'])->name('gestor-horarios.index');
        
        Route::post('aulas/{aula}/asignaturas', [\App\Http\Controllers\AulaAsignaturaController::class, 'store'])->name('aulas.asignaturas.store');
        Route::put('aulas/{aula}/asignaturas/{asignatura}', [\App\Http\Controllers\AulaAsignaturaController::class, 'update'])->name('aulas.asignaturas.update');
        
        // HORARIOS DEL AULA
        Route::get('aulas/{aula}/horarios', [\App\Http\Controllers\HorarioController::class, 'index'])->name('aulas.horarios.index');
        Route::post('aulas/{aula}/horarios', [\App\Http\Controllers\HorarioController::class, 'store'])->name('aulas.horarios.store');
        Route::delete('aulas/{aula}/horarios/{horario}', [\App\Http\Controllers\HorarioController::class, 'destroy'])->name('aulas.horarios.destroy');
        
        Route::resource('malla', \App\Http\Controllers\MallaCurricularController::class)
        ->only(['index', 'store', 'destroy']);
        
        // Bloques de Modalidades (Antes Horario General)
        Route::resource('bloques', \App\Http\Controllers\BloqueHorarioController::class)
            ->only(['index', 'store', 'destroy']);

        // --- VISOR DE HORARIOS (Solo Lectura) ---
        Route::prefix('visor-horarios')->name('visor.')->group(function () {
            Route::get('/', [\App\Http\Controllers\VisorHorarioController::class, 'index'])->name('index');
            Route::get('/docentes', [\App\Http\Controllers\VisorHorarioController::class, 'docentes'])->name('docentes');
            Route::get('/docentes/{docente}', [\App\Http\Controllers\VisorHorarioController::class, 'horarioDocente'])->name('docente.show');
            Route::get('/aulas', [\App\Http\Controllers\VisorHorarioController::class, 'aulas'])->name('aulas');
            Route::get('/aulas/{aula}', [\App\Http\Controllers\VisorHorarioController::class, 'horarioAula'])->name('aula.show');
        });
        // ----------------------------------------

        Route::put('usuarios/{usuario}/reset-password', [\App\Http\Controllers\UsuarioController::class, 'resetPassword'])
             ->name('usuarios.reset-password');
        Route::resource('usuarios', \App\Http\Controllers\UsuarioController::class);

        // Fase 5: Calificaciones
        Route::get('notas', [\App\Http\Controllers\NotaController::class, 'index'])->name('notas.index');
        Route::get('notas/planilla/{asignacion}', [\App\Http\Controllers\NotaController::class, 'create'])->name('notas.create');
        Route::post('notas/{asignacion}', [\App\Http\Controllers\NotaController::class, 'store'])->name('notas.store');
        
        // --- NUEVAS RUTAS DE BLOQUEO Y AUDITORÍA ---
        Route::post('notas/{asignacion}/cerrar', [\App\Http\Controllers\NotaController::class, 'cerrarParcial'])->name('notas.cerrar');
        Route::post('notas/{asignacion}/solicitar-desbloqueo', [\App\Http\Controllers\NotaController::class, 'solicitarDesbloqueo'])->name('notas.solicitar-desbloqueo');

        // Resolución de solicitudes de edición (Dirección / Subdirección)
        Route::get('notas/solicitudes', [\App\Http\Controllers\SolicitudEdicionNotaController::class, 'index'])->name('notas.solicitudes.index');
        Route::patch('notas/solicitudes/{solicitud}/aprobar', [\App\Http\Controllers\SolicitudEdicionNotaController::class, 'aprobar'])->name('notas.solicitudes.aprobar');
        Route::patch('notas/solicitudes/{solicitud}/rechazar', [\App\Http\Controllers\SolicitudEdicionNotaController::class, 'rechazar'])->name('notas.solicitudes.rechazar');
        
        Route::get('cortes-evaluativos', [\App\Http\Controllers\CorteEvaluativoController::class, 'index'])->name('cortes.index');
        Route::put('cortes-evaluativos/{corte}', [\App\Http\Controllers\CorteEvaluativoController::class, 'update'])->name('cortes.update');
    
        Route::get('notas/actividades/{asignacion}', [\App\Http\Controllers\ActividadEvaluativaController::class, 'index'])->name('notas.actividades.index');
        Route::post('notas/actividades/{asignacion}', [\App\Http\Controllers\ActividadEvaluativaController::class, 'store'])->name('notas.actividades.store');
        Route::put('notas/actividades/{asignacion}/{actividad}', [\App\Http\Controllers\ActividadEvaluativaController::class, 'update'])->name('notas.actividades.update');
        Route::delete('notas/actividades/{asignacion}/{actividad}', [\App\Http\Controllers\ActividadEvaluativaController::class, 'destroy'])->name('notas.actividades.destroy');

        // Fase 6: Asistencia (Docente Guía)
        Route::get('asistencia/aula', [\App\Http\Controllers\AsistenciaAulaController::class, 'create'])->name('asistencia.aula.create');
        Route::post('asistencia/aula', [\App\Http\Controllers\AsistenciaAulaController::class, 'store'])->name('asistencia.aula.store');
        // Fase 6: Asistencia (Docente por Asignatura - Por Excepción)
        Route::get('asistencia/asignatura/{asignacion}', [\App\Http\Controllers\AsistenciaAsignaturaController::class, 'create'])->name('asistencia.asignatura.create');
        Route::post('asistencia/asignatura/{asignacion}', [\App\Http\Controllers\AsistenciaAsignaturaController::class, 'store'])->name('asistencia.asignatura.store');
        Route::delete('asistencia/asignatura/{asignacion}/incidencia/{incidencia}', [\App\Http\Controllers\AsistenciaAsignaturaController::class, 'destroy'])->name('asistencia.asignatura.destroy');

        // Fase 7: Boletines
        Route::get('boletines', [\App\Http\Controllers\BoletinController::class, 'index'])->name('boletines.index');
        Route::get('boletines/{matricula}', [\App\Http\Controllers\BoletinController::class, 'show'])->name('boletines.show');

        // Fase 7: Examen de Reparación
        Route::get('reparacion', [\App\Http\Controllers\ExamenReparacionController::class, 'index'])->name('reparacion.index');
        Route::post('reparacion', [\App\Http\Controllers\ExamenReparacionController::class, 'store'])->name('reparacion.store');
        Route::delete('reparacion/{examen}', [\App\Http\Controllers\ExamenReparacionController::class, 'destroy'])->name('reparacion.destroy');

        // Fase 7: Avance de Contenidos
        Route::get('avance-contenido', [\App\Http\Controllers\AvanceContenidoController::class, 'index'])->name('avance.index');
        Route::post('avance-contenido', [\App\Http\Controllers\AvanceContenidoController::class, 'store'])->name('avance.store');
        Route::delete('avance-contenido/{avance}', [\App\Http\Controllers\AvanceContenidoController::class, 'destroy'])->name('avance.destroy');

        // Fase 7: Apoyo de Padres
        Route::get('apoyo-padres', [\App\Http\Controllers\ApoyoPadresController::class, 'index'])->name('apoyo-padres.index');
        Route::post('apoyo-padres', [\App\Http\Controllers\ApoyoPadresController::class, 'store'])->name('apoyo-padres.store');
        Route::delete('apoyo-padres/{apoyo}', [\App\Http\Controllers\ApoyoPadresController::class, 'destroy'])->name('apoyo-padres.destroy');

        // Fase 8: Control Disciplinario
        Route::get('disciplina', [\App\Http\Controllers\IncidenciaDisciplinariaController::class, 'index'])->name('disciplina.index');
        Route::post('disciplina', [\App\Http\Controllers\IncidenciaDisciplinariaController::class, 'store'])->name('disciplina.store');
        Route::put('disciplina/{incidencia}', [\App\Http\Controllers\IncidenciaDisciplinariaController::class, 'update'])->name('disciplina.update');
        // Fase 8: Centro de Reportes
        Route::get('reportes', [\App\Http\Controllers\ReporteController::class, 'index'])->name('reportes.index');

        // Control de ingreso de notas
        Route::get('reportes/control-notas', [\App\Http\Controllers\ReporteController::class, 'controlNotas'])->name('reportes.control-notas');
        Route::get('reportes/notas-globales', [\App\Http\Controllers\ReporteController::class, 'notasGlobales'])->name('reportes.notas-globales');
        Route::get('reportes/notas-pendientes', [\App\Http\Controllers\ReporteController::class, 'notasPendientes'])->name('reportes.notas-pendientes');

        // Asistencia (segmentada)
        Route::get('reportes/asistencia-global', [\App\Http\Controllers\ReporteController::class, 'asistenciaGlobal'])->name('reportes.asistencia-global');
        Route::get('reportes/estadisticas-asistencia', [\App\Http\Controllers\ReporteController::class, 'estadisticasAsistencia'])->name('reportes.estadisticas-asistencia');
        Route::get('reportes/asistencia-seccion-dia', [\App\Http\Controllers\ReporteController::class, 'asistenciaSeccionDia'])->name('reportes.asistencia-seccion-dia');
        Route::get('reportes/asistencia-seccion-rango', [\App\Http\Controllers\ReporteController::class, 'asistenciaSeccionRango'])->name('reportes.asistencia-seccion-rango');
        Route::get('reportes/asistencia-estudiante', [\App\Http\Controllers\ReporteController::class, 'estadisticasPorEstudiante'])->name('reportes.asistencia-estudiante');

        // Rendimiento académico
        Route::get('reportes/notas-por-asignatura', [\App\Http\Controllers\ReporteController::class, 'notasPorAsignatura'])->name('reportes.notas-por-asignatura');
        Route::get('reportes/historial-estudiante', [\App\Http\Controllers\ReporteController::class, 'historialPorEstudiante'])->name('reportes.historial-estudiante');

        // Otros reportes (MINED, estudiantes, padres)
        Route::get('reportes/mined', [\App\Http\Controllers\ReporteController::class, 'mined'])->name('reportes.mined');
        Route::get('reportes/estudiantes', [\App\Http\Controllers\ReporteController::class, 'estudiantes'])->name('reportes.estudiantes');
        Route::get('reportes/padres', [\App\Http\Controllers\ReporteController::class, 'padres'])->name('reportes.padres');
    });
});

require __DIR__.'/auth.php';