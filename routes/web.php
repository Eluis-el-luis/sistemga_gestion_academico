<?php

use App\Http\Controllers\Academico\AnioEscolarController;
use App\Http\Controllers\ActividadEvaluativaController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\ApoyoFamiliarController;
use App\Http\Controllers\ApoyoPadresController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\AsistenciaAsignaturaController;
use App\Http\Controllers\AsistenciaAulaController;
use App\Http\Controllers\AsistenciaPersonalController;
use App\Http\Controllers\AulaAsignaturaController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AvanceContenidoController;
use App\Http\Controllers\BloqueHorarioController;
use App\Http\Controllers\BoletinController;
use App\Http\Controllers\BoletinPdfController;
use App\Http\Controllers\CorteEvaluativoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocenteGuiaController;
use App\Http\Controllers\ExamenReparacionController;
use App\Http\Controllers\GestorBoletinController;
use App\Http\Controllers\GrupoMateriaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\IncidenciaDisciplinariaController;
use App\Http\Controllers\MallaCurricularController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PrematriculaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RespaldoController;
use App\Http\Controllers\SolicitudEdicionNotaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VisorHorarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/sin-rol', fn () => view('auth.sin-rol'))
    ->middleware('auth')
    ->name('sin-rol');

Route::middleware(['auth', 'role'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('anio.modificable')->group(function () {
        Route::post('/dashboard/avisos', [DashboardController::class, 'storeAviso'])->name('dashboard.avisos.store');
        Route::put('/dashboard/avisos/{id}', [DashboardController::class, 'updateAviso'])->name('dashboard.avisos.update');
        Route::delete('/dashboard/avisos/{id}', [DashboardController::class, 'destroyAviso'])->name('dashboard.avisos.destroy');
        Route::post('/asistencia-personal/marcar', [AsistenciaPersonalController::class, 'marcarLlegada'])->name('asistencia.personal.marcar');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role'])->prefix('academico')->name('academico.')->group(function () {
    Route::get('asistencia/personal', [AsistenciaPersonalController::class, 'index'])->name('asistencia.personal.index');

    // Usuarios y personal
    Route::middleware('rol.acceso:directora,subdirectora,gestor_it,secretaria')->group(function () {
        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');

        Route::middleware('anio.modificable')->group(function () {
            Route::get('usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
            Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
            Route::get('usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
            Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
            Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
            Route::put('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');
        });
    });

    // Alumnos y matrículas (lectura)
    Route::middleware('rol.acceso:directora,subdirectora,secretaria,coordinador,docente_guia,docente_asignatura,docente_auxiliar,docente_suplente,psicologa')->group(function () {
        Route::get('alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
        Route::get('matriculas', [MatriculaController::class, 'index'])->name('matriculas.index');
    });

    // Alumnos, matrículas y prematrícula (escritura)
    Route::middleware(['rol.acceso:directora,subdirectora,secretaria,coordinador,docente_guia', 'anio.modificable'])->group(function () {
        Route::get('alumnos/create', [AlumnoController::class, 'create'])->name('alumnos.create');
        Route::post('alumnos', [AlumnoController::class, 'store'])->name('alumnos.store');
        Route::get('alumnos/{alumno}/edit', [AlumnoController::class, 'edit'])->name('alumnos.edit');
        Route::put('alumnos/{alumno}', [AlumnoController::class, 'update'])->name('alumnos.update');
        Route::delete('alumnos/{alumno}', [AlumnoController::class, 'destroy'])->name('alumnos.destroy');

        Route::get('matriculas/create', [MatriculaController::class, 'create'])->name('matriculas.create');
        Route::post('matriculas', [MatriculaController::class, 'store'])->name('matriculas.store');
        Route::get('matriculas/{matricula}/edit', [MatriculaController::class, 'edit'])->name('matriculas.edit');
        Route::put('matriculas/{matricula}', [MatriculaController::class, 'update'])->name('matriculas.update');
        Route::delete('matriculas/{matricula}', [MatriculaController::class, 'destroy'])->name('matriculas.destroy');

        Route::patch('matriculas/{matricula}/retirar', [MatriculaController::class, 'retirar'])->name('matriculas.retirar');
        Route::patch('matriculas/{matricula}/reactivar', [MatriculaController::class, 'reactivar'])->name('matriculas.reactivar');
        Route::get('prematricula', [PrematriculaController::class, 'index'])->name('prematricula.index');
        Route::post('prematricula/{matricula}/promover', [PrematriculaController::class, 'promover'])->name('prematricula.promover');
        Route::post('prematricula/{matricula}/remitir', [PrematriculaController::class, 'remitir'])->name('prematricula.remitir');
    });

    // Detalle de alumno (después de alumnos/create para evitar colisiones)
    Route::middleware('rol.acceso:directora,subdirectora,secretaria,coordinador,docente_guia,docente_asignatura,docente_auxiliar,docente_suplente,psicologa')->group(function () {
        Route::get('alumnos/{alumno}', [AlumnoController::class, 'show'])->name('alumnos.show');
    });

    // Estructura académica, aulas, asignaciones, horarios y grupos de materia
    Route::middleware('rol.acceso:directora,subdirectora,coordinador,gestor_it')->group(function () {
        Route::get('anios-escolares/activo', [AnioEscolarController::class, 'activo'])->name('anios-escolares.activo');
        Route::get('anios-escolares/{anioEscolar}/validaciones-cierre', [AnioEscolarController::class, 'validacionesCierre'])->name('anios-escolares.validaciones-cierre');
        Route::resource('anios-escolares', AnioEscolarController::class)
            ->only(['index', 'show'])
            ->parameters(['anios-escolares' => 'anioEscolar']);

        Route::resource('asignaturas', AsignaturaController::class)->only(['index']);
        Route::resource('malla', MallaCurricularController::class)->only(['index']);
        Route::resource('bloques', BloqueHorarioController::class)->only(['index']);
        Route::resource('aulas', AulaController::class)->only(['index', 'show']);
        Route::get('asignaciones', [AulaController::class, 'indexAsignaciones'])->name('asignaciones.index');
        Route::get('asignaciones/{aula}', [AulaController::class, 'showAsignaciones'])->name('asignaciones.show');
        Route::get('gestor-horarios', [AulaController::class, 'indexHorarios'])->name('gestor-horarios.index');
        Route::get('grupo-materia', [GrupoMateriaController::class, 'index'])->name('grupo-materia.index');
    });

    Route::middleware(['rol.acceso:directora,subdirectora,coordinador,gestor_it', 'anio.modificable'])->group(function () {
        Route::resource('anios-escolares', AnioEscolarController::class)
            ->only(['create', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['anios-escolares' => 'anioEscolar']);
        Route::put('anios-escolares/{anioEscolar}/cambiar-estado', [AnioEscolarController::class, 'cambiarEstado'])->name('anios-escolares.cambiar-estado');

        Route::resource('asignaturas', AsignaturaController::class)->only(['store', 'update', 'destroy']);
        Route::put('malla/grado/{grado}/horas', [MallaCurricularController::class, 'actualizarHorasGrado'])->name('malla.grado.horas');
        Route::resource('malla', MallaCurricularController::class)->only(['store', 'destroy']);
        Route::put('malla/item/{id}', [MallaCurricularController::class, 'update'])->name('malla.item.update');
        Route::post('malla/clonar', [MallaCurricularController::class, 'clonarMalla'])->name('malla.clonar');

        Route::resource('bloques', BloqueHorarioController::class)->only(['store', 'destroy']);
        Route::post('bloques/clonar', [BloqueHorarioController::class, 'clonar'])->name('bloques.clonar');
        Route::post('bloques/generar-masivo', [BloqueHorarioController::class, 'generarMasivo'])->name('bloques.generar-masivo');
        Route::delete('bloques/jornada/eliminar', [BloqueHorarioController::class, 'destroyJornada'])->name('bloques.jornada.destroy');

        Route::resource('aulas', AulaController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::post('asignaciones/masivo', [AulaAsignaturaController::class, 'asignarMasivo'])->name('asignaciones.masivo');
        Route::post('aulas/{aula}/asignaturas', [AulaAsignaturaController::class, 'store'])->name('aulas.asignaturas.store');
        Route::put('aulas/{aula}/asignaturas/{asignatura}', [AulaAsignaturaController::class, 'update'])->name('aulas.asignaturas.update');
        Route::delete('aulas/{aula}/asignaturas/{asignacion}', [AulaAsignaturaController::class, 'destroyAsignatura'])->name('aulas.asignaturas.destroy');

        Route::get('aulas/{aula}/horarios', [HorarioController::class, 'index'])->name('aulas.horarios.index');
        Route::post('aulas/{aula}/horarios', [HorarioController::class, 'store'])->name('aulas.horarios.store');
        Route::delete('aulas/{aula}/horarios/{horario}', [HorarioController::class, 'destroy'])->name('aulas.horarios.destroy');

        Route::post('grupo-materia', [GrupoMateriaController::class, 'store'])->name('grupo-materia.store');
        Route::put('grupo-materia/{grupo}', [GrupoMateriaController::class, 'update'])->name('grupo-materia.update');
        Route::delete('grupo-materia/{grupo}', [GrupoMateriaController::class, 'destroy'])->name('grupo-materia.destroy');
        Route::post('grupo-materia/asignar', [GrupoMateriaController::class, 'asignarMaterias'])->name('grupo-materia.asignar');
    });

    // Visor de horarios
    Route::prefix('visor-horarios')->name('visor.')->group(function () {
        Route::get('/', [VisorHorarioController::class, 'index'])->name('index');
        Route::get('/docentes', [VisorHorarioController::class, 'docentes'])->name('docentes');
        Route::get('/docentes/{docente}', [VisorHorarioController::class, 'horarioDocente'])->name('docente.show');
        Route::get('/aulas', [VisorHorarioController::class, 'aulas'])->name('aulas');
        Route::get('/aulas/{aula}', [VisorHorarioController::class, 'horarioAula'])->name('aula.show');
    });

    // Docente guía
    Route::prefix('tutor')->name('tutor.')->middleware('rol.acceso:docente_guia,coordinador,subdirectora,directora')->group(function () {
        Route::get('mis-alumnos', [DocenteGuiaController::class, 'misAlumnos'])->name('mis-alumnos');
        Route::get('rendimiento', [DocenteGuiaController::class, 'rendimiento'])->name('rendimiento');
        Route::get('mi-horario', [DocenteGuiaController::class, 'miHorario'])->name('horario');
        Route::get('detalles-mi-aula', [DocenteGuiaController::class, 'detallesMiAula'])->name('detalles-mi-aula');
    });

    // Calificaciones, actividades y cortes
    Route::get('notas', [NotaController::class, 'index'])->name('notas.index');
    Route::get('notas/planilla/{asignacion}', [NotaController::class, 'create'])->name('notas.create');
    Route::get('notas/evaluar/{asignacion}', [NotaController::class, 'evaluar'])->name('notas.evaluar');
    Route::get('cortes-evaluativos', [CorteEvaluativoController::class, 'index'])->name('cortes.index');
    Route::get('notas/actividades/{asignacion}', [ActividadEvaluativaController::class, 'index'])->name('notas.actividades.index');
    Route::get('notas/solicitudes', [SolicitudEdicionNotaController::class, 'index'])->name('notas.solicitudes.index');

    Route::middleware('anio.modificable')->group(function () {
        Route::post('notas/{asignacion}', [NotaController::class, 'store'])->name('notas.store');
        Route::post('notas/{asignacion}/cerrar', [NotaController::class, 'cerrarParcial'])->name('notas.cerrar');
        Route::post('notas/{asignacion}/solicitar-desbloqueo', [NotaController::class, 'solicitarDesbloqueo'])->name('notas.solicitar-desbloqueo');
        Route::patch('notas/solicitudes/{solicitud}/aprobar', [SolicitudEdicionNotaController::class, 'aprobar'])->name('notas.solicitudes.aprobar');
        Route::patch('notas/solicitudes/{solicitud}/rechazar', [SolicitudEdicionNotaController::class, 'rechazar'])->name('notas.solicitudes.rechazar');
        Route::put('cortes-evaluativos/{corte}', [CorteEvaluativoController::class, 'update'])->name('cortes.update');
        Route::post('notas/actividades/{asignacion}', [ActividadEvaluativaController::class, 'store'])->name('notas.actividades.store');
        Route::put('notas/actividades/{asignacion}/{actividad}', [ActividadEvaluativaController::class, 'update'])->name('notas.actividades.update');
        Route::delete('notas/actividades/{asignacion}/{actividad}', [ActividadEvaluativaController::class, 'destroy'])->name('notas.actividades.destroy');
    });

    // Asistencia de aula y asignatura
    Route::get('asistencia/aula', [AsistenciaAulaController::class, 'create'])->name('asistencia.aula.create');
    Route::get('asistencia/asignatura/{asignacion}', [AsistenciaAsignaturaController::class, 'create'])->name('asistencia.asignatura.create');

    Route::middleware('anio.modificable')->group(function () {
        Route::post('asistencia/aula', [AsistenciaAulaController::class, 'store'])->name('asistencia.aula.store');
        Route::post('asistencia/asignatura/{asignacion}', [AsistenciaAsignaturaController::class, 'store'])->name('asistencia.asignatura.store');
        Route::delete('asistencia/asignatura/{asignacion}/incidencia/{incidencia}', [AsistenciaAsignaturaController::class, 'destroy'])->name('asistencia.asignatura.destroy');
    });

    // Boletines y PDFs
    Route::get('boletines/bandeja', [GestorBoletinController::class, 'bandeja'])->name('boletin.bandeja');
    Route::get('boletines', [BoletinController::class, 'index'])->name('boletines.index');
    Route::get('boletines/constancia/{matricula}', [BoletinController::class, 'constancia'])->name('boletines.constancia');
    Route::get('boletines/{matricula}', [BoletinController::class, 'show'])->name('boletines.show');
    Route::post('boletines/{matricula}/aprobar', [BoletinController::class, 'aprobarBoletin'])
        ->middleware('anio.modificable')
        ->name('boletines.aprobar');

    Route::get('boletines/pdf/boletin/{matricula}', [BoletinPdfController::class, 'boletin'])->name('boletines.pdf.boletin');
    Route::get('boletines/pdf/constancia/{matricula}', [BoletinPdfController::class, 'constancia'])->name('boletines.pdf.constancia');
    Route::get('boletines/pdf/certificado/{matricula}', [BoletinPdfController::class, 'certificado'])->name('boletines.pdf.certificado');
    Route::match(['get', 'post'], 'boletines/pdf/masivo', [BoletinPdfController::class, 'masivo'])->name('boletines.pdf.masivo');

    // Seguimiento académico y disciplina
    Route::get('reparacion', [ExamenReparacionController::class, 'index'])->name('reparacion.index');
    Route::get('avance-contenido', [AvanceContenidoController::class, 'index'])->name('avance.index');
    Route::get('apoyo-padres', [ApoyoPadresController::class, 'index'])->name('apoyo-padres.index');
    Route::get('apoyo-familiar', [ApoyoFamiliarController::class, 'index'])->name('apoyo_familiar.index');
    Route::get('disciplina', [IncidenciaDisciplinariaController::class, 'index'])->name('disciplina.index');

    Route::middleware('anio.modificable')->group(function () {
        Route::post('reparacion', [ExamenReparacionController::class, 'store'])->name('reparacion.store');
        Route::delete('reparacion/{examen}', [ExamenReparacionController::class, 'destroy'])->name('reparacion.destroy');

        Route::post('avance-contenido', [AvanceContenidoController::class, 'store'])->name('avance.store');
        Route::delete('avance-contenido/{avance}', [AvanceContenidoController::class, 'destroy'])->name('avance.destroy');

        Route::post('apoyo-padres', [ApoyoPadresController::class, 'store'])->name('apoyo-padres.store');
        Route::delete('apoyo-padres/{apoyo}', [ApoyoPadresController::class, 'destroy'])->name('apoyo-padres.destroy');
        Route::post('apoyo-familiar', [ApoyoFamiliarController::class, 'store'])->name('apoyo_familiar.store');

        Route::post('disciplina', [IncidenciaDisciplinariaController::class, 'store'])->name('disciplina.store');
        Route::put('disciplina/{incidencia}', [IncidenciaDisciplinariaController::class, 'update'])->name('disciplina.update');
    });

    // Reportes
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/control-notas', [ReporteController::class, 'controlNotas'])->name('control-notas');
        Route::get('/notas-globales', [ReporteController::class, 'notasGlobales'])->name('notas-globales');
        Route::get('/notas-pendientes', [ReporteController::class, 'notasPendientes'])->name('notas-pendientes');
        Route::get('/asistencia-global', [ReporteController::class, 'asistenciaGlobal'])->name('asistencia-global');
        Route::get('/estadisticas-asistencia', [ReporteController::class, 'estadisticasAsistencia'])->name('estadisticas-asistencia');
        Route::get('/asistencia-seccion-dia', [ReporteController::class, 'asistenciaPorSeccion'])->name('asistencia-seccion-dia');
        Route::get('/asistencia-seccion-rango', [ReporteController::class, 'asistenciaSeccionRango'])->name('asistencia-seccion-rango');
        Route::get('/asistencia-estudiante', [ReporteController::class, 'estadisticasPorEstudiante'])->name('asistencia-estudiante');
        Route::get('/mis-clases', [ReporteController::class, 'misClases'])->name('mis-clases');
        Route::get('/mi-aula', [ReporteController::class, 'miAula'])->name('mi-aula');
        Route::get('/notas-por-asignatura', [ReporteController::class, 'notasPorAsignatura'])->name('notas-por-asignatura');
        Route::get('/rendimiento-corte', [ReporteController::class, 'rendimientoCorte'])->name('rendimiento-corte');
        Route::get('/historial-estudiante', [ReporteController::class, 'historialPorEstudiante'])->name('historial-estudiante');
        Route::get('/mined', [ReporteController::class, 'mined'])->name('mined');
        Route::get('/mined-detallado', [ReporteController::class, 'reporteMinedDetallado'])->name('mined-detallado');
        Route::get('/avance-programatico', [ReporteController::class, 'avanceProgramatico'])->name('avance-programatico');
        Route::get('/asignaturas-aprobadas', [ReporteController::class, 'asignaturasAprobadas'])->name('asignaturas-aprobadas');
        Route::get('/estudiantes', [ReporteController::class, 'estudiantes'])->name('estudiantes');
        Route::get('/padres', [ReporteController::class, 'padres'])->name('padres');
        Route::get('/escalas', [ReporteController::class, 'escalas'])->name('escalas');
    });

    // Respaldos
    Route::prefix('respaldos')->name('respaldos.')->middleware('rol.acceso:directora,subdirectora')->group(function () {
        Route::get('/', [RespaldoController::class, 'index'])->name('index');
        Route::post('/', [RespaldoController::class, 'store'])->middleware('anio.modificable')->name('store');
        Route::get('/{nombre}/descargar', [RespaldoController::class, 'descargar'])->name('descargar');
        Route::post('/{nombre}/restaurar', [RespaldoController::class, 'restaurar'])
            ->middleware(['anio.modificable', 'rol.acceso:directora'])
            ->name('restaurar');
        Route::delete('/{nombre}', [RespaldoController::class, 'destroy'])
            ->middleware(['anio.modificable', 'rol.acceso:directora'])
            ->name('destroy');
    });
});

require __DIR__.'/auth.php';