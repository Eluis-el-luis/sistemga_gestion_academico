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

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/dashboard/avisos', [\App\Http\Controllers\DashboardController::class, 'storeAviso'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.avisos.store');

Route::put('/dashboard/avisos/{id}', [\App\Http\Controllers\DashboardController::class, 'updateAviso'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.avisos.update');

Route::delete('/dashboard/avisos/{id}', [\App\Http\Controllers\DashboardController::class, 'destroyAviso'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.avisos.destroy');

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
        Route::resource('aulas', AulaController::class);
        Route::post('aulas/{aula}/asignaturas', [\App\Http\Controllers\AulaAsignaturaController::class, 'store'])->name('aulas.asignaturas.store');
        Route::put('aulas/{aula}/asignaturas/{asignatura}', [\App\Http\Controllers\AulaAsignaturaController::class, 'update'])->name('aulas.asignaturas.update');
        // HORARIOS DEL AULA
        Route::get('aulas/{aula}/horarios', [\App\Http\Controllers\HorarioController::class, 'index'])->name('aulas.horarios.index');
        Route::post('aulas/{aula}/horarios', [\App\Http\Controllers\HorarioController::class, 'store'])->name('aulas.horarios.store');
        Route::delete('aulas/{aula}/horarios/{horario}', [\App\Http\Controllers\HorarioController::class, 'destroy'])->name('aulas.horarios.destroy');
        Route::resource('malla', \App\Http\Controllers\MallaCurricularController::class)
        ->only(['index', 'store', 'destroy']);
        // Plantilla Oficial: Bloques de Horario General
        Route::resource('bloques', \App\Http\Controllers\BloqueHorarioController::class)
            ->only(['index', 'store', 'destroy']);

        Route::put('usuarios/{usuario}/reset-password', [\App\Http\Controllers\UsuarioController::class, 'resetPassword'])
             ->name('usuarios.reset-password');
        Route::resource('usuarios', \App\Http\Controllers\UsuarioController::class);

    });

});


require __DIR__.'/auth.php';
