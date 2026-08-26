<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\MallaCurricularController;

Route::get('/', function () {
    return view('welcome');
});

// --- RUTAS DEL PANEL PRINCIPAL Y TABLERO DE AVISOS ---
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Gestión de Avisos
Route::post('/dashboard/avisos', [\App\Http\Controllers\DashboardController::class, 'storeAviso'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.avisos.store');

Route::put('/dashboard/avisos/{id}', [\App\Http\Controllers\DashboardController::class, 'updateAviso'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.avisos.update');

Route::delete('/dashboard/avisos/{id}', [\App\Http\Controllers\DashboardController::class, 'destroyAviso'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.avisos.destroy');
// -----------------------------------------------------

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    
    Route::prefix('academico')->name('academico.')->group(function () {
        
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
        Route::post('notas', [\App\Http\Controllers\NotaController::class, 'store'])->name('notas.store');
        Route::get('cortes-evaluativos', [\App\Http\Controllers\CorteEvaluativoController::class, 'index'])->name('cortes.index');
        Route::put('cortes-evaluativos/{corte}', [\App\Http\Controllers\CorteEvaluativoController::class, 'update'])->name('cortes.update');
    
        Route::get('notas/actividades/{asignacion}', [\App\Http\Controllers\ActividadEvaluativaController::class, 'index'])->name('notas.actividades.index');
        Route::post('notas/actividades/{asignacion}', [\App\Http\Controllers\ActividadEvaluativaController::class, 'store'])->name('notas.actividades.store');
        Route::delete('notas/actividades/{asignacion}/{actividad}', [\App\Http\Controllers\ActividadEvaluativaController::class, 'destroy'])->name('notas.actividades.destroy');

        // Fase 6: Asistencia (Docente Guía)
        Route::get('asistencia/aula', [\App\Http\Controllers\AsistenciaAulaController::class, 'create'])->name('asistencia.aula.create');
        Route::post('asistencia/aula', [\App\Http\Controllers\AsistenciaAulaController::class, 'store'])->name('asistencia.aula.store');
        // Fase 6: Asistencia (Docente por Asignatura - Por Excepción)
        Route::get('asistencia/asignatura/{asignacion}', [\App\Http\Controllers\AsistenciaAsignaturaController::class, 'create'])->name('asistencia.asignatura.create');
        Route::post('asistencia/asignatura/{asignacion}', [\App\Http\Controllers\AsistenciaAsignaturaController::class, 'store'])->name('asistencia.asignatura.store');
        Route::delete('asistencia/asignatura/incidencia/{incidencia}', [\App\Http\Controllers\AsistenciaAsignaturaController::class, 'destroy'])->name('asistencia.asignatura.destroy');
    });
});

require __DIR__.'/auth.php';