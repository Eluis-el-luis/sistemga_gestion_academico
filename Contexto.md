# Contexto técnico del sistema de gestión académica

## 1. Propósito del proyecto

Este repositorio corresponde a una aplicación monolítica de gestión académica escolar construida con Laravel 13 y Blade. El sistema modela la operación completa de un centro educativo: administración de usuarios, matrícula, aulas, asignaturas, malla curricular, notas, asistencia, boletines, reportes, apoyo pedagógico y control disciplinario.

La arquitectura no es una API REST independiente; la aplicación utiliza sesiones web, vistas Blade y controladores tradicionales, con un enfoque de dominio académico muy específico y una capa de autorización basada en roles y permisos de Spatie.

La solución está orientada a un perfil institucional con usuarios del tipo Director, Subdirector, Docente Guía, Docente por Asignatura, Coordinador, Secretario y Alumno. La lógica de negocio está muy acoplada al contexto escolar, por lo que el dominio se define en términos de aulas, cortes evaluativos, asignaciones por docente, matrículas y registro de indicadores de logro.

## 2. Stack tecnológico

### Backend

- PHP 8.3
- Laravel Framework 13.8
- Eloquent ORM
- Illuminate Auth + Session guard
- Spatie Laravel Permission 8.3
- Laravel Sanctum 4.3
- Laravel Tinker
- Barryvdh/Laravel-Dompdf
- Maatwebsite/Excel

### Frontend

- Vite 8
- Tailwind CSS 3.1
- Alpine.js 3.4.2 / 3.16.1
- PostCSS y Autoprefixer
- Blade templates + CSS modularizado con utilities de Tailwind

### Infraestructura y calidad

- PHPUnit para pruebas
- Laravel Pint para formateo
- Laravel Pail para logs en desarrollo
- Composer scripts para entorno local

## 3. Estructura general del repositorio

La organización sigue la convención de Laravel, pero con un dominio académico muy específico:

- app/Http/Controllers: flujo HTTP, validación de entrada, orquestación de consultas y vistas
- app/Http/Requests: reglas de validación para módulos sensibles
- app/Models: modelos de dominio con nombres de tabla explícitos
- app/Policies: autorización por recurso y por capacidad
- app/Services: lógica de negocio reutilizable, especialmente en calificaciones
- database/migrations: esquema relacional con convenciones propias del colegio
- database/seeders: datos base, permisos y roles
- resources/views: interfaz en Blade por módulos académicos
- resources/js: inicialización del frontend cliente
- resources/css: estilos globales y Tailwind
- routes/web.php: router principal de la aplicación
- routes/auth.php: autenticación y recuperación de contraseña
- config/auth.php: configuración de guard y provider de autenticación
- config/permission.php: configuración de Spatie Permission

## 4. Convenciones de dominio y persistencia

### 4.1 Nombres de tablas y modelos

Una característica central del proyecto es que los modelos usan nombres de tablas en español y en singular, y algunas propiedades se definen explícitamente con `$table`.

Ejemplos observados:

- `Usuario` => `usuario`
- `Alumno` => `alumno`
- `Docente` => `docente`
- `Aula` => `aula`
- `Matricula` => `matricula`
- `Asignatura` => `asignatura`
- `CorteEvaluativo` => `corte_evaluativo`
- `AulaAsignaturaDocente` => `aula_asignatura_docente`
- `BloqueHorario` => `bloque_horario`
- `IndicadorLogro` => `indicador_logro`

Esto implica que la aplicación no se apoya en convenciones plurales automáticas de Laravel; se ha diseñado para un esquema académico específico y no genérico. Es relevante para:

- route model binding
- Eloquent relationships
- foreign keys
- consultas manuales y joins
- migraciones futuras

### 4.2 Soft deletes

El sistema usa SoftDeletes en modelos clave como `Usuario`, `Alumno`, `Docente`, `Matricula` y otros. Esto no debe confundirse con la lógica de negocio de "estado activo/inactivo". El flujo institucional mantiene una separación clara entre:

- registro físico eliminado en base de datos (`deleted_at`)
- estado funcional del registro (`estado`, `activo`, `retirado`, etc.)

Esto es fundamental porque ciertos módulos consultan registros activos, pero la capa de persistencia no necesariamente los elimina físicamente de la base.

### 4.3 Claves foráneas y relaciones

La base está diseñada con identificadores relacionales explícitos y con relaciones de negocio que no siempre coinciden con la convención por defecto de Laravel. Por ejemplo:

- `Matricula` refiere a `alumno_id`, `aula_id`, `anio_escolar_id`
- `AulaAsignaturaDocente` refiere a `aula_id`, `asignatura_id`, `docente_id`, `anio_escolar_id`
- `Nota` refiere a `matricula_id`, `aula_asignatura_docente_id`, `corte_evaluativo_id`
- `ActividadEvaluativa` refiere a `aula_asignatura_docente_id`
- `AsistenciaAsignatura` refiere a `matricula_id`, `asignatura_id`, `bloque_horario_id`

Estas relaciones están centradas en la lógica escolar de la institución, no en un esquema CRUD genérico.

## 5. Autenticación y autorización

### 5.1 Guard principal

La configuración de `config/auth.php` define el guard `web`, con provider Eloquent apuntando a `App\Models\Usuario`.

`Usuario` extiende `Authenticatable` de Laravel, incorpora `HasRoles` de Spatie, `HasApiTokens`, `Notifiable` y `SoftDeletes`, y además implementa `MustVerifyEmail`.

Esto convierte al modelo `Usuario` en el principal actor de autenticación del sistema.

### 5.2 Roles y permisos

El sistema usa Spatie Permission para la autorización. Los roles y permisos están dados en `database/seeders/PermisoSeeder.php`.

Roles principales:

- Director
- Subdirector
- Docente Guia
- Docente por Asignatura
- Coordinador
- Gestor de Usuarios
- Secretaria
- Alumno

Permisos principales:

- alumnos.gestionar / alumnos.ver / alumnos.supervisar
- asignaturas_aula.gestionar / asignaturas_aula.ver
- notas.gestionar / notas.ver
- asistencia.gestionar / asistencia.ver
- indicadores.gestionar / indicadores.ver
- boletines.gestionar / boletines.ver
- malla.gestionar / malla.ver
- avance.gestionar / avance.ver
- apoyo_padres.gestionar / apoyo_padres.ver
- reparacion.gestionar / reparacion.ver
- reportes.gestionar / reportes.ver / reportes.supervisar
- configuracion.gestionar / configuracion.ver

### 5.3 Policies

La capa de soporte de autorización se implementa a través de policies en `app/Policies`, por ejemplo:

- `AulaAsignaturaDocentePolicy`
- `AsistenciaAulaPolicy`
- `NotaPolicy`
- `MatriculaPolicy`
- `UsuarioPolicy`
- `AulaPolicy`
- `HorarioPolicy`

Ejemplo clave: `AulaAsignaturaDocentePolicy` usa `before()` para conceder permiso absoluto a Director y Subdirector, y luego valida el acceso por permiso y por pertenencia del docente a la asignatura.

La regla general del proyecto es: el rol operativo define la capacidad, y la policy valida el alcance del recurso. Esto es especialmente importante en operaciones como:

- calificar notas
- gestionar asistencia de aula
- editar horarios
- supervisar matrículas
- revisar boletines

## 6. Rutas y estructura de navegación HTTP

El router principal se encuentra en `routes/web.php` y define dos grandes bloques:

### 6.1 Rutas públicas y de sesión

- `/` redirige a login
- `/dashboard`
- `/profile`
- rutas de Breeze para autenticación, verificación, recuperación y restablecimiento de contraseña

### 6.2 Grupo académico con prefijo `/academico`

La mayor parte del sistema vive bajo:

- `Route::middleware('auth')`
- `Route::prefix('academico')`
- `Route::name('academico.')`

Este paquete incluye módulos de:

- alumnos
- matrículas
- aulas y asignaciones
- horarios y visor
- malla curricular
- bloques de horario
- notas y actividades evaluativas
- cortes evaluativos
- asistencia por aula y asignatura
- boletines y constancias
- exámenes de reparación
- avance de contenido
- apoyo de padres
- disciplina
- reportes
- gestión de usuarios
- grupo-materias

El diseño del router es un reflejo del dominio escolar y define claramente los puntos de entrada de cada operación.

## 7. Modelo de negocio principal

### 7.1 Usuario

El modelo `Usuario` es la entidad central del sistema. Representa al usuario autenticado del portal institucional.

Relaciones clave:

- `docente()` => un usuario puede ser un docente
- `alumno()` => un usuario puede ser un alumno
- `asistencias()` => registros de asistencia personal

Además, el modelo está encargado de la identidad digital y de la validación de sesión, lo que hace que sea el eje de intersección entre autenticación y dominio académico.

### 7.2 Alumno

`Alumno` concentra el expediente académico y personal del estudiante. En la práctica, es la entidad que captura:

- datos personales básicos
- familiares o autorizados
- datos de salud
- registro religioso o de convivencia
- asociatividad con matrículas y asistencia

La matrícula de un alumno no es simplemente una relación de tabla; es un vínculo de continuidad con el historial escolar.

### 7.3 Docente

`Docente` representa la identidad funcional del profesor dentro del sistema. Se asocia a un usuario y tiene una relación muy fuerte con la operación de curso, ya que participa en:

- asignaciones de aula / materia / docente
- gestión de notas
- registro de asistencia
- tutoría de aula
- supervisión de horarios

### 7.4 Aula y asignación académica

La entidad central del sistema académico es `AulaAsignaturaDocente`.

Representa la relación de una asignatura que se imparte dentro de un aula, por un docente concreto, durante un año escolar y con una carga horaria determinada. Esta entidad es la base sobre la que se construyen muchos procesos:

- calificaciones
- asistencia por materia
- horarios
- indicadores de logro
- carga docente
- reportes de rendimiento

El modelo `Aula` también es central porque almacena la relación con:

- grado
- modalidad
- año escolar
- docente guía

### 7.5 Matricula

`Matricula` conecta a un alumno con una aula y con un año escolar. El estado de la matrícula define si el alumno se encuentra activo, retirado o reactivado. El sistema usa ese estado para filtrar alumnos en planillas y para controlar el acceso a evaluaciones y reportes.

### 7.6 Nota y evaluación

La capa de evaluación se compone de varias entidades:

- `CorteEvaluativo`: define los parciales/periodos del año
- `ActividadEvaluativa`: define actividades dentro de un parcial
- `NotaActividad`: guarda la calificación individual de esa actividad por alumno
- `Nota`: representa el consolidado final del alumno en una asignatura + corte
- `IndicadorLogro`: convierte la nota cuantitativa en la escala cualitativa del sistema escolar

El servicio `NotaService` encapsula la lógica de conversión y promedios (escala oficial MINED vigente 2026):

- 90 a 100 => AA (Aprendizaje Avanzado)
- 76 a 89 => AS (Aprendizaje Satisfactorio)
- 60 a 75 => AE (Aprendizaje Esperado) — antes AF (Aprendizaje Fundamental), renombrado globalmente en Oct 2026 (código, catálogo `indicador_logro`, seeder, factory y migración `2026_10_14_update_indicador_logro_af_a_ae`)
- 0 a 59 => AI (Aprendizaje Inicial)

Además, calcula promedios semestrales y anuales, y establece aprobado cuando la nota final es mayor o igual a 60.

**Nota Final del Corte (0-100):** se calcula escalando la suma de puntos obtenidos contra el **total oficial del corte** (`peso_acumulado + peso_examen`, normalmente 100), no contra la suma de puntajes configurados: las actividades no configuradas aún valen 0. Esta escala se usa tanto al guardar (`NotaController@store`, `ActividadEvaluativaController::recalcularNotasParcial` — también recalcula al editar el puntaje máximo de una actividad) como en el cálculo en vivo de las vistas `notas/evaluar` y `notas/planilla` (columna "Nota Final del Corte N", donde N = número del corte).

## 8. Flujos de negocio principales

### 8.1 Gestión de usuarios y acceso

El sistema prepara el contexto de identidad y permisos antes de acceder a cualquier módulo. En la práctica:

- el usuario inicia sesión con el guard web
- se valida su rol y permisos Spatie
- la UI se adapta según la identidad del usuario
- los controladores llaman a `authorize()` o verifican permisos explícitos

La lógica de roles no está limitada solamente a UI; también puede tener impacto real en la consulta de registros y en la edición de datos académicos.

### 8.2 Matrícula y expediente

La matrícula es un proceso de negocio de alto impacto porque establece la relación entre estudiante, curso y ciclo escolar. El módulo de matriculación permite:

- crear matrícula
- actualizar matrícula
- retirar matrícula
- reactivar matrícula
- consultar expediente y resultados del estudiante

Además, el sistema combina datos del alumno, aula, modalidad y plan académico para generar un panorama operativo completo.

### 8.3 Aulas y asignaturas

Este módulo es la base del plan de estudios:

- asignación de docentes a aulas
- gestión de asignaturas por aula
- carga horaria semanal
- configuración de horarios
- consulta de aulas por docente y por grado

`AulaAsignaturaDocente` es el punto nodal del modelo académico y muchos otros procesos dependen de ella.

### 8.4 Horarios

La aplicación incluye un gestor y un visor de horarios. La estructura permite:

- gestionar bloques por modalidad
- asignar horario a una asignación docente
- ver disponibilidad por docente o aula
- consultar el mapa horario escolar

La lógica se apoya en `BloqueHorario` y en la relación con `AulaAsignaturaDocente`.

### 8.5 Calificaciones

El flujo de notas es probablemente el módulo más estrictamente regulado del sistema. En `NotaController` se observan varios patrones importantes:

- autorización previa a la operación
- validación del corte evaluativo
- bloqueo del parcial mediante `CorteCerrado`
- transacción sobre los cambios
- cálculo automático del total acumulado por alumno
- persistencia de notas individuales y nota consolidada por corte

Esto indica una lógica de negocio sólida para evitar inconsistencias en los registros de notas, especialmente cuando se trabaja con planillas masivas.

### 8.6 Asistencia

La asistencia se implementa en dos niveles:

- asistencia por aula
- asistencia por asignatura

La asistencia por aula está diseñada para el docente guía, con foco en el grupo completo del aula y sus matrículas activas. La asistencia por asignatura está centrada en el docente que imparte la materia y puede considerar incidencias puntuales por clase, fecha y bloque horario.

El diseño usa `updateOrCreate` para evitar duplicados y está atado a claves de negocio como:

- `matricula_id`
- `asignatura_id`
- `bloque_horario_id`
- `fecha`

Esto hace que un mismo estudiante no tenga dos incidencias duplicadas para la misma clase.

### 8.7 Boletines y reportes

El sistema incluye procesos de cierre de ciclo y generación de boletines, constancias, certificaciones y reportes de rendimiento. Hay controllers especializados para:

- `BoletinController`
- `BoletinPdfController`
- `GestorBoletinController`
- `ReporteController`

Estos reportes se basan en el historial académico y en los estados de matrícula y notas generadas por las asignaturas y cortes evaluativos.

**Formatos de salida (Oct 2026):**

- **Boletín** y **Constancia de notas**: carta HORIZONTAL (landscape), una página por documento. **Certificado de notas**: carta VERTICAL.
- Las vistas web con impresión (horarios por aula/docente, reportes) imprimen en horizontal vía `@page landscape`.
- Todos los PDF se generan con `isRemoteEnabled = false` y el logo embebido en base64 (`BoletinPdfController::logoDataUri`); nunca se cargan recursos vía HTTP durante el render (eso causaba un deadlock con `php artisan serve`, y el request colgado bloqueaba la sesión dejando toda la app sin responder hasta reiniciar el equipo).
- `BoletinPdfController::masivo` libera el bloqueo de sesión antes de generar el ZIP y amplía límites de tiempo/memoria.

**Certificado de Notas (formato MINED)** — `academico/reportes/historial-estudiante`:
- Flujo en 3 pasos: buscar estudiante (nombre/CUP) → seleccionarlo → elegir año + grado entre sus matrículas reales.
- `ReporteService::certificadoNotas()` agrupa el historial: grado seleccionado (en el año elegido, se muestre aprobado o reprobado) + grados inmediatamente anteriores de la misma modalidad. El límite es la constante `ReporteService::CERTIFICADO_LIMITE_GRADOS` (2 por defecto), fácilmente modificable.
- La NF de años pasados se promedia con los cortes del año real de cada matrícula (no el año activo).
- Tabla comparativa por asignaturas con NF cuantitativa (0-100) y cualitativa (AA/AS/AE/AI) por grado, promedio por grado, datos del centro y firma.

**Rendimiento por Corte — Formato MINED "III CORTE 2026"** — `ReporteService::rendimientoCorteMined()`:
- Una tabla por modalidad (Preescolar/Primaria/Secundaria) con fila TOTAL, todas las métricas partidas en AS (ambos sexos) y F (femenino): MI, MA, % DE PERM (MA/MI), APROB (0 materias reprobadas), % DE APROB (APROB/evaluados), APLAZ 1-2, APLAZ 3-MAS, EVALUADO PERIODO OFICIAL (MA con notas en el corte), EVALUADO PERIODO EXTRA (con examen de reparación en el rango del corte/año), NO EVALUADO (= MA − evaluados), ASIST REFORZ y MAEST INT REF (0; no hay módulo de reforzamiento).
- Sección ESCALA de rendimiento por modalidad: conteo de estudiantes por banda cualitativa del promedio del corte (AA/AS/AE/AI) + VALOR (= total evaluados).
- Supuestos de cálculo documentados en el docblock del método.
- **Estado tras el merge del 23 Oct 2026:** el método y su test (`RendimientoMined2026Test`) siguen vigentes como servicio, pero la ruta `reportes/rendimiento-corte` ya NO lo usa: el diseño de la rama `feauture/no` (controller-centric, filas por grado + gráfica Chart.js + `$chartData`) lo reemplazó. Si se quiere recuperar el formato por modalidad, el servicio está listo.

### 8.7.1 Merge de `feauture/no` (23 Oct 2026) — diseño mejorado de reportes

**Estrategia acordada:** el trabajo de `feauture/no` es el DISEÑO (vistas/UI/UX); nuestro trabajo de fase 8 es la BASE lógica. El merge se resolvió adoptando su diseño completo y adaptando nuestros controladores/servicios para alimentarlo; su código muerto o con bugs se corrigió con base en nuestro código. Quedó como merge commit `06935bf`.

**Lo que llegó de `feauture/no`:**
- Reportes nuevos: `mi-aula` (docente guía), `escalas`, `avance-programatico`, `asignaturas-aprobadas` (+ rutas y métodos de controlador inline).
- Rediseño de vistas existentes: `index` (hub), `mined` (formato oficial con logos `mined_izq/mined_der.png`, MI/MA por sexo, fila TOTAL y firmas), `notas-globales` (sábana por aula, requerido, excluye TIC/Biblia/Informática), `notas-pendientes`, `control-notas` (auditoría de notas + avance con `tipo_auditoria`/`perspectiva`), `rendimiento-corte`, `notas-por-asignatura`, `estadisticas-asistencia`/`seccion-rango` (filas por FECHA + `promedio_rango`), `asistencia-por-seccion` (nueva, reemplaza al reporte "seccion-dia"), `padres`, `estudiantes`, dashboard (directiva/docente-guía), formularios de aulas y matrículas.
- **Cupos de matrícula**: `MatriculaController@store` bloquea la matrícula cuando `matriculas_count >= aula->cupo`, y tras guardar redirige a `matriculas.create` (UX para matrículas en cadena; test de fase 4 adaptado).
- `ReporteController@index` usa `pasarCatalogos($request, false)` (no carga docentes/aulas/alumnos en el hub).
- Servicio: refactor con `queryAsignaciones()` (1 helper con TODOS los filtros: aula/asignatura/docente/grado/modalidad — conserva el contrato de filtros de fase 8), `auditoriaAvanceContenido()`, `asistenciaSeccionRango()`, `asistenciaEstudiante()` (con búsqueda por nombre), `estadisticasFechas()`.

**Conflictos del merge y cómo se resolvieron:**
1. `ReporteService.php` (3 conflictos): `notasGlobalesPorAlumno` → HÍBRIDO (sus estructuras y forma de fila `matricula_id/alumno/cup/notas/promedio_general/reprobadas` + nuestros filtros flexibles por docente/modalidad/grado; sin su guard que devolvía vacío sin aula); `notasPendientes` y `estadisticasAsistencia` → suyos (su refactor optimizado; los filtros viven en `queryAsignaciones`/`whereHas`).
2. `control-notas`, `filtros-notas`, `rendimiento-corte` (vistas) → suyos (diseño).
3. Adaptaciones controlador↔vista hechas a mano (en su rama las vistas nuevas consumían variables que sus controladores NO pasaban; aquí quedaron coherentes): `notasGlobales` pasa `asignaturas/filas/aula` (el merge de variables pone el catálogo PRIMERO para que las columnas filtradas pisen al catálogo), `notasPendientes` pasa `filas`, `padres`/`estadisticasAsistencia`/`asistenciaSeccionRango`/`asistenciaPorSeccion`/`estadisticasPorEstudiante` propagan el arreglo completo del servicio, `asistenciaSeccionDia` ahora despacha al método `asistenciaPorSeccion` (su renombrado) hacia la vista `asistencia-por-seccion`.

**Bugs de su rama corregidos (base: nuestro trabajo):**
1. El `reject` de asignaturas excluía por SUBSTRING (`str_contains('TIC')`), lo que mataba "Matemá**tic**a" y "Estadís**tic**a" → ahora coincidencia exacta normalizando acentos (`TIC`, `BIBLIA`, `INFORMATICA`).
2. `resumenIngresoNotas()` usaba `count(distinct concat(...))` — **`concat()` no existe en SQLite** (la suite de tests revientaba con 500→302 en el hub) y en PostgreSQL exigiría casts → conteo de pares únicos en PHP (`get([...])->unique(fn ...)`), agnóstico al motor.
3. Regla de la lección: en este proyecto el SQL crudo debe funcionar en pgsql (Supabase) y SQLite (tests) a la vez; evitar `concat()`, `ilike` sin guard, etc. (`ilike` solo aparece en `asistenciaEstudiante` cuando se envía `busqueda` — no se usa en los tests).

**Tests adaptados al nuevo diseño (suite final: 160 tests / 491 aserciones, en verde):**
- `AlumnoMatriculaTest::test_procesar_matricula` → espera redirect a `matriculas.create` (UX de matrículas masivas).
- `ReporteFiltrosTest::test_estadisticas_asistencia_filtra_por_modalidad` → crea registros reales de `AsistenciaAula` (no hay factory) y verifica el filtro por modalidad con filas por fecha.
- `ReporteFiltrosTest::test_notas_pendientes_filtra_por_modalidad` → nueva forma de retorno (arreglo de filas con `estudiante`).

### 8.8 Apoyo y disciplina

La aplicación también incluye módulos orientados a soporte institucional y convivencia:

- `ApoyoPadresController`
- `ApoyoFamiliarController`
- `IncidenciaDisciplinariaController`
- `AvanceContenidoController`
- `ExamenReparacionController`

Esto evidencia que el proyecto no es únicamente una gestión de calificaciones; actúa como un sistema de operación escolar completo.

## 9. Patrones de implementación observados

### 9.1 Controller-centric business logic

La lógica principal de la aplicación se concentra en controladores. Esto es típico de Laravel, pero en este proyecto se ha ido estructurando en servicios según complejidad. El caso más evidente es `NotaService`, usado por `NotaController` para encapsular lógica de notas y conversiones.

### 9.2 Uso de Policies como capa de restricciones

El proyecto no depende solo del guard, sino que combina varios niveles de control:

- `middleware('auth')` en rutas
- `authorize()` en controladores
- checks de rol (`hasRole`) en vistas y lógicas de negocio
- checks de permiso (`hasPermissionTo`) en policies

Esto es una práctica sólida, pero requiere disciplina porque si se agrega una ruta nueva sin policy, se abre un riesgo de fuga de acceso.

### 9.3 Recursos y formularios por módulo

La UI se construye desde vistas Blade con fragmentación por módulos. La estructura es muy clara:

- layouts para el shell base
- vistas de índice por entidad
- formularios para creación/edición
- paneles de reportes y gestión

### 9.4 Dependencia de datos de contexto escolar

El sistema no usa solo IDs; muchos procesos dependen de contexto operativo explícito, como:

- año escolar activo
- modalidad
- aula
- docente guía
- corte evaluativo vigente
- estado de matrícula

Eso hace que la validación de entrada sea crítica para evitar que un usuario consulte o edite información fuera de su contexto curricular.

## 10. Configuración del frontend

La capa frontend está basada en Blade + Tailwind + Alpine.js. El layout principal incluye:

- navegación institucional
- sidebar responsive
- persistencia del estado del menú en localStorage
- carga de librerías externas para gráficos y alertas

El archivo de configuración relevante es `package.json`, que define:

- Vite como bundler
- Tailwind como utility framework
- Alpine como JS para interactividad local
- `concurrently` para levantar varios procesos simultáneamente

Este enfoque facilita el desarrollo rápido de módulos dentro del monolito, aunque hay dependencia de recursos CDN como SweetAlert2 o Chart.js en el layout, lo que requiere una decisión explícita de arquitectura y mantenimiento.

## 11. Pruebas y calidad del código

El proyecto tiene un conjunto inicial de pruebas basado en Laravel, pero no se trata de una suite exhaustiva del dominio académico. La lógica crítica está en módulos como notas, asistencia y permisos, por lo que la cobertura debería priorizar:

1. autorización por rol y permiso
2. validación de ownership en recursos académicos
3. cálculo de indicadores de logro
4. operación por lotes de notas
5. idempotencia de asistencia
6. filtros por aula, gestión de matrículas activas y año escolar
7. tests de regresión para rutas críticas

Lo importante es no tratar la validación y autorización como un detalle opcional; en este proyecto, son una parte esencial del modelo de seguridad.

### 11.1 Campaña de pruebas integral (Oct 2026)

Estado de la suite regular (`php artisan test`, SQLite en memoria): **160 tests / 491 aserciones, todos en verde** (actualizado tras el merge de `feauture/no` del 23 Oct 2026; incluye los tests de módulos de las fases 2-8 y del módulo de respaldos).

| Módulo | Archivo de prueba | Cobertura |
|---|---|---|
| Servicios de notas (MINED 2026) | `Unit/NotaServiceTest` | Indicadores AA/AS/AE/AI, semestral, final |
| Autenticación Breeze | `Feature/Auth/*` | Login, reset, verificación, confirmación |
| Reportes | `Feature/Reporte*Test` | 14 reportes, filtros, certificado MINED |
| Idempotencia | `Feature/IdempotenciaTest` | Notas y asistencia por claves de negocio |
| Fase 7 (reparación/avance/apoyo) | `Feature/Fase7Test` | Registro y reglas |
| **Usuarios (Fase 2)** | `Feature/UsuarioModuleTest` | CRUD, jerarquía (Gestor no ve Dirección), blindajes de policies, reset de contraseña, validaciones |
| **Aulas y asignaciones (Fase 3)** | `Feature/AulaModuleTest` | Apertura con clonación de malla (11 materias para 1ro), anti-duplicados (regla en `StoreAulaRequest`), docente guía único por año, re-sincronización al cambiar grado, coordinador por modalidad, eliminación en cascada, materia extra, asignación masiva, choque de horario al reasignar docente |
| **Malla, bloques y horarios (Fase 3)** | `Feature/MallaBloquesHorarioTest` | Límite de horas por grado, duplicados en malla, autoincremento de bloques, recreo sin número, bloque en uso no se elimina, choques de aula y de maestro, recreo no programable, materia sin docente, permisos `horarios.gestionar` vs `horarios.ver` |
| **Alumnos, matrículas y prematrícula (Fase 4)** | `Feature/AlumnoMatriculaTest` | Expediente (creación solo Dirección), código único, borrado lógico, bloqueo de doble matrícula por año, formulario excluye matriculados, retiro/reactivación (jerarquía: Secretaria no retira), promoción de año (aula destino, denegado en reparación) |
| **Notas (Fase 5)** | `Feature/NotasModuleTest` | Policy `calificar` (titular/guía sí, tercero no), límites por tipo vs peso del corte (acumulado 60/examen 40), edición y eliminación de actividades con recalculo, bloqueo por corte cerrado, escalado MINED en el endpoint, pesos de corte suman 100, validación de fechas |

**Bugs de aplicación encontrados y corregidos por la campaña:**
1. `UsuarioController@store/update`: fuga de transacción al fallar la autorización (refactor a `DB::transaction()`).
2. `PrematriculaController@promover`: retornos tempranos dentro de `DB::beginTransaction()` sin commit/rollBack (mismo refactor).
3. `ActividadEvaluativaController@store`: no llamaba `authorize('calificar')` — cualquier usuario autenticado podía crear actividades. Corregido.
4. `HorarioController@store/destroy`: se tragaban las `AuthorizationException` (302 en vez de 403). Corregido.

**Deuda de autorización detectada (pendiente de decisión):** `PrematriculaController` (index/promover/remitir) no llama `authorize()`; solo exige perfil de docente guía en `index`. `promover` es alcanzable por cualquier usuario autenticado — conviene definir la política esperada (¿solo Docente Guía del aula? ¿Dirección?) y añadir el chequeo.

Regla de diseño para pruebas de estructura: las aulas de prueba se aperturan con `AulaService::crearAulaConMalla()` (no con `Aula::create()`), porque la clonación de malla es responsabilidad del servicio.

## 12. Operación local y despliegue

El flujo de arranque recomendado, según el proyecto, es:

```bash
composer install
php artisan key:generate
php artisan migrate --force
npm install --ignore-scripts
npm run build
```

Además, el script `composer run dev` levanta simultáneamente:

- PHP server
- queue listener
- pail logs
- Vite dev server

La aplicación está preparada para correr de forma local en un entorno Laravel típico, pero la producción requiere revisión de:

- `APP_ENV`
- `APP_DEBUG`
- `APP_KEY`
- `APP_URL`
- base de datos real
- sesión, cache y cola
- almacenamiento persistente
- correo transaccional
- logs centralizados
- pipeline CI/CD
- backups
- supervisión de errores y rendimiento

## 13. Riesgos técnicos y deuda identificada

### 13.1 Riesgos de seguridad y autorización

Los puntos críticos a vigilar son:

- nuevas rutas sin policy asociada
- validación de propiedad implementada inline
- uso inconsistente de roles y permisos
- acceso a recursos por ID sin filtros de contexto
- falta de homogeneidad entre roles declarados en DB y roles esperados por negocio

### 13.2 Riesgos de consistencia del dominio

El esquema académico usa model names y tablas particulares. Si se alteran columnas, nombres de modelos o relaciones, cada operación del proyecto puede romperse. Por eso cada cambio de dominio debe evaluarse como un cambio de contrato de negocio y no como un ajuste puntual.

### 13.3 Riesgos de acoplamiento de UI y negocio

La aplicación usa vistas Blade y controladores con lógica de estado y validación. Esto hace que el código crezca rápido, y sin disciplina de servicio, las responsabilidades se mezclan. Para futuras mejoras, es recomendable separar mejor:

- validación de entrada
- autorización
- consultas de dominio
- presentación

## 14. Recomendaciones para mantenimiento profesional

1. Consolidar una sola fuente de verdad para roles y permisos.
2. Mantener una práctica estricta de `authorize()` en todos los puntos de escritura.
3. Evitar validaciones de propiedad inline y trasladarlas a policies.
4. Mantener un único criterio para el estado activo/inactivo de matrículas y registros eliminados.
5. Asegurar que cada módulo tenga tests de regresión.
6. Revisar la estructura de migraciones antes de cambiar claves foráneas o nombres de tablas.
7. Separar lógica de dominio de lógica HTTP cuando el módulo lo requiera.
8. Documentar claramente la diferencia entre `deleted_at`, `estado` y `activo` en el dominio escolar.
9. No asumir que la presencia de Sanctum implica una API REST disponible.
10. Priorizar comprobación de permisos sobre comprobaciones de UI.

## 15. Pruebas E2E con Laravel Dusk (Oct 2026)

El proyecto cuenta con una suite de pruebas end-to-end reales (navegador Chrome + Selenium/ChromeDriver) que recorre los flujos de negocio completos por la interfaz. Esta sección documenta su infraestructura, convenciones y reglas críticas.

### 15.1 Stack y archivos

- **Laravel Dusk 8.7** (ya estaba en `composer.json`, require-dev) sobre **php-webdriver**; ChromeDriver viene incluido en `vendor/laravel/dusk/bin` y `DuskTestCase::prepare()` lo levanta en el puerto 9515.
- `.env.dusk`: entorno dedicado de pruebas E2E (`APP_ENV=dusk`, `APP_URL=http://127.0.0.1:8299`, **SQLite en archivo** `database/dusk.sqlite`, sesión `file`, correo `array`, `E2E_SEED=1`). **NUNCA apunta a la BD real de Supabase.**
- `phpunit.dusk.xml`: configuración PHPUnit de la suite Browser. Importante: los comentarios XML no pueden contener `--` (rompe el parseo).
- `database/seeders/E2eSeeder.php`: siembra **catálogos base** (los mismos de `DatabaseSeeder`); idempotente.
- `database/seeders/E2eScenarioSeeder.php`: siembra el **escenario de negocio** (cuentas de todos los roles con dominio `*.e2e@ccn.test`, password `password`, docentes, aula "1ro A" con asignaciones y 3 alumnos matriculados). Idempotente; se ejecuta en cada test.
- `tests/Browser/E2eTestCase.php`: clase base. Verifica que el servidor dedicado esté arriba, gestiona la BD con el trait `DatabaseTruncation` (primer test: `migrate:fresh` + escenario; siguientes: trunca solo tablas de negocio preservando catálogos y tablas de Spatie vía `exceptTables`) y provee helpers: `loginComo` (limpia la cookie de sesión antes de loguear porque Dusk reutiliza el navegador), `comoInvitado`, `logoutDe` (POST real `/logout` con el CSRF del layout), `marcarRadio` (radios `sr-only` por JS) y `abrirModal` (despacha el evento de ventana que usa la UI Alpine).

### 15.2 Cómo ejecutar

```bash
# Terminal 1: servidor dedicado (obligatorio; los tests NO lo levantan solos
# porque en Windows un proceso hijo de PHPUnit cuelga la ejecución)
php artisan serve --env=dusk --host=127.0.0.1 --port=8299 --no-reload

# Terminal 2: correr la suite E2E
php artisan dusk
```

Atajos: `composer e2e` (levanta el servidor con `start /B` y corre `php artisan dusk`). El script `scripts/activar-wal-dusk.php` activa **WAL** sobre `dusk.sqlite` (una sola vez por archivo): sin WAL, los INSERT en SQLite/Windows cuestan ~0.25 s c/u y la suite tarda 3-4 veces más.

### 15.3 Los flujos cubiertos (8 tests)

| Archivo (tests/Browser) | Flujo | Actor |
|---|---|---|
| `ExampleTest.php` | Humo: login válido/inválido, redirección de invitados, logout real | Director |
| `CicloAcademicoCompletoTest.php` | Aperturar aula (clona malla de 1ro: 11 asignaturas), asignar docente masivo, registrar alumno, matricular, crear actividad, evaluar notas, cerrar parcial, ver boletín | Director + Docente por Asignatura |
| `FlujoDocenteTest.php` | Panel del tutor (mis alumnos), asistencia de aula con estados, evaluación con actividad acumulada (20 pts) + examen (40 pts), verificación del escalado MINED (NF = suma/100; 60 => AE, 28 => AI) y rendimiento | Docente Guía |
| `FlujoDesbloqueoTest.php` | Cerrar parcial → solicitar desbloqueo (modal) → Dirección aprueba (elimina `CorteCerrado`) → docente corrige nota; y el rechazo (el parcial permanece cerrado) | Docente + Director/Subdirectora |
| `FlujoReparacionTest.php` | Alumno de 7mo (los grados 1-5 son auto-promovidos y NO reparan) con nota anual reprobada → Dirección registra examen de reparación (acordeón Alpine) → queda `aprobado` → el PDF del boletín genera (200 + `application/pdf`) | Director |

### 15.4 Reglas críticas aprendidas (leer antes de escribir tests E2E)

1. **Base de datos**: el navegador habla con el servidor (`.env.dusk`); el proceso de tests apunta a la misma BD por las variables de `phpunit.dusk.xml`. Nunca mezclar con `.env` (Supabase).
2. **`--no-reload` es obligatorio**: `php artisan serve` vigila la carpeta `database/` y se reinicia con cada escritura en `dusk.sqlite`, causando `ERR_CONNECTION_REFUSED` intermitente.
3. **No levantar el servidor desde PHPUnit**: el proceso hijo cuelga la ejecución en Windows (la tool espera al árbol completo).
4. **`assertSee` compara contra `innerText` (texto renderizado)**: los títulos con clase Tailwind `uppercase` se ven "MAYÚSCULAS" aunque el HTML tenga mayúsculas/minúsculas. Aserción segura: nombres propios sin clase uppercase, o `mb_strtoupper($texto)`.
5. **Modales y acordeones Alpine**: los formularios viven en contenedores `x-show` (modales de cerrar parcial, solicitud de desbloqueo, acordeón de reparación). Abrirlos con `abrirModal($browser, 'abrir-modal-cierre')` (despacha el mismo evento que el botón) o por JS; de lo contrario WebDriver falla con "element not interactable".
6. **Radios `sr-only`**: marcarlos con `marcarRadio` (JS `.click()`), no con `radio()` de Dusk ("element click intercepted").
7. **Selectores de formulario ambiguos**: en una misma página puede haber un form GET de filtros y el POST de acción con URLs similares (`asistencia/aula`). Desambiguar con `[method="POST"]`.
8. **Fechas fijas** en pruebas de asistencia (`?fecha=2026-10-19`): "hoy" implícito se rompe si la suite cruza la medianoche.
9. **Parámetros de corte**: la página de actividades filtra con `?corte_id=N` y la planilla/evaluar con `?corte_evaluativo_id=N`. Por defecto eligen el **primer** corte, no el vigente por fecha.
10. **`waitUsing` enmascara el error real**: preferir `waitForText('<flash de éxito>')` + aserción de BD directa en el cuerpo del test.
11. Los flashes de éxito usados por los tests: "Actividad guardada correctamente", "Calificaciones actualizadas", "Calificaciones cerradas", "Solicitud enviada", "Solicitud aprobada", "Solicitud rechazada", "Examen de reparación registrado correctamente".
12. **ChromeDriver tarda ~3 s en enlazar el puerto 9515** y `startChromeDriver` no espera: los primeros tests fallarían con "Failed to connect to localhost port 9515". `DuskTestCase::prepare()` ya incluye una espera activa del puerto (fsockopen hasta 20 s).
13. `.env.dusk` y `.env.dusk.local` están en `.gitignore` (pueden contener APP_KEY y credenciales). En un clon nuevo, crear `.env.dusk` copiando `.env.example` y ajustando: `APP_ENV=dusk`, `APP_URL=http://127.0.0.1:8299`, `DB_CONNECTION=sqlite` con `DB_DATABASE=<ruta absoluta a database/dusk.sqlite>`, `SESSION_DRIVER=file`, `MAIL_MAILER=array`, `E2E_SEED=1`.

### 15.5 Deuda detectada por las pruebas E2E

- El mojibake en algunas vistas/seeders (`GestiA3n`, `SA-,`) es solo de visualización de consola; en BD los textos son UTF-8 limpios. El `login.blade.php` sí tiene texto fuente dañado (cosmético).
- La tabla `actividad_evaluativa` conserva columna `tipo` (una migración la agrega y otra la elimina; el controlador escribe en ella).
- `composer e2e` usa `start /B` de cmd: si el puerto 8299 queda ocupado por una ejecución anterior, cerrar ese proceso antes de reintentar.

### 15.6 INCIDENTE y salvaguardas de base de datos (Oct 2026) — LEER SÍ O SÍ

**Ocurrió:** al correr `php artisan dusk` desde una terminal con variables `DB_*` heredadas, PHPUnit (sin `force`) no sobreescribió esas variables, el entorno resolvió la conexión **pgsql (Supabase)** y `migrate:fresh` de `DatabaseTruncation` ejecutó `db:wipe` **sobre la BD real** (borró todas las tablas) antes de fallar al intentar cargar `database/schema/pgsql-schema.sql` con `psql`. Ese archivo (vacío) era el detonante: con conexión pgsql, `migrate:fresh` intenta cargar el dump vía binario `psql`.

**Causas combinadas:** (1) `<env>` de PHPUnit sin `force="true"` no gana a variables del shell; (2) un dump `pgsql-schema.sql` presente en `database/schema/` activa la carga por `psql`; (3) no existía verificación runtime de la conexión activa.

**Salvaguardas aplicadas (defensa en profundidad):**
1. `database/schema/pgsql-schema.sql` eliminado (estaba en 0 bytes).
2. `phpunit.xml` y `phpunit.dusk.xml`: TODAS las variables `env` usan `force="true"` (ganan a cualquier shell).
3. `tests/TestCase.php`: guarda que aborta si la conexión activa no es SQLite `:memory:`.
4. `tests/Browser/E2eTestCase::setUp`: guarda que aborta si la conexión no es `database/dusk.sqlite`.
5. **Centinela de login** en `setUpBeforeClass`: antes de que cualquier test escriba por la UI, hace un login HTTP real contra el servidor dedicado; si falla, el servidor NO está leyendo `dusk.sqlite` y se aborta todo. El escenario E2E se siembra (subproceso `migrate:fresh --seed` + `db:seed --class=E2eScenarioSeeder`) ANTES del centinela, para que el usuario del centinela exista.
6. `scripts/servidor-e2e.cmd`: lanzador que (a) FUERZA las variables `DB_*` a SQLite antes de arrancar `php artisan serve --env=dusk` y (b) usa el PHP de Laragon completo, no el del PATH (el php del PATH puede ser otra versión sin extensiones → errores 500 por `openssl`). `composer e2e` lo usa. Arrancar el servidor SIEMPRE con este script.
7. `DuskTestCase::prepare()` es autorreparable: si ChromeDriver murió entre clases, lo re-lanza y espera el puerto (2 intentos); si sigue caído, falla con mensaje claro.
8. `E2eTestCase::servidorResponde()` reintenta 3 veces (timeout 8 s): el `php -S` es mono-hilo y una conexión keep-alive del navegador puede retrasar `/up` varios segundos (falsos "no hay servidor").

**Estado final (Oct 2026):** la BD de Supabase fue restaurada por el equipo (44 tablas verificadas en `public`) y se implementó un **módulo de backup** propio para prepararse ante futuros incidentes. El incidente quedó documentado como caso de estudio: ninguna prueba automatizada debe poder alcanzar jamás la BD real; las salvaguardas de esta sección lo impiden en tres capas (config `force`, guardas de conexión y centinela de login).

**Regla inviolable:** jamás correr `migrate`, `db:seed`, `db:wipe` ni `migrate:fresh` sin verificar antes la conexión activa (`php artisan config:show database.default`). En este proyecto, las pruebas automatizadas SOLO corren sobre SQLite.

## 16. Módulo de Respaldos de la Base de Datos (Oct 2026) — LEER SÍ O SÍ

Creado como respuesta directa al incidente de la sección 15.6 (se perdió el contenido de la BD de Supabase y solo existían las migraciones para recuperar la estructura). Este módulo protege el CONTENIDO.

### 16.1 Diseño del motor (`app/Services/RespaldoService.php`)

- **100% PHP, sin binarios externos**: en Laragon local no existe `pg_dump`/`psql` (Laragon solo trae MySQL/MariaDB) y el plan de Supabase no ofrece backups automáticos; el volcado se genera leyendo las tablas vía PDO y escribiendo sentencias SQL estándar.
- **Formato del volcado** (`.sql` dentro de un `.zip` en `storage/app/respaldos/`): cabecera de metadatos (`-- SGA-RESPALDO v1`, FECHA, MOTOR, HOST, BASE, GENERADO_POR, MOTIVO, TABLAS, FILAS con ancho fijo de 12 dígitos), una sola transacción `BEGIN … COMMIT`, DELETEs en orden INVERSO de dependencias de FKs, INSERTs multi-row (200 filas/statement, configurable) en orden TOPOLOGICO de FKs (algoritmo de Kahn, padres primero; ante un ciclo, el resto se vuelca alfabético) y `setval` para sincronizar secuencias (solo pgsql).
- **Restauración atómica**: se ejecuta el SQL completo en UNA sola llamada `DB::unprepared()` (consulta multi-statement con `BEGIN/COMMIT` embebidos). Si un statement falla, Postgres revierte TODO (el `COMMIT` final sobre transacción abortada actúa como rollback). Esto además es compatible con el pooler de Supabase en modo transacción (puerto 6543): la consulta entera viaja por una única conexión física.
- **Compatible con pgsql (Supabase) y SQLite (tests)**: introspección por `information_schema` o `PRAGMA` según driver; el quoting de valores usa el `PDO::quote` del motor activo (booleans como `'1'/'0'` citado: válido en ambos motores).
- **Retención**: al crear un respaldo se eliminan los que excedan `BACKUP_RETENCION` (default 20). Nombres únicos: si dos respaldos caen en el mismo segundo, se numera (`respaldo_..._2.zip`).
- **Validación de nombres**: `RespaldoService::PATRON_NOMBRE` estricto (regex) + `basename()`, evita path traversal en las rutas `{nombre}`.

### 16.2 Salvaguardas de restauración (lecciones del incidente)

1. `restaurar()` rechaza el archivo si el MOTOR del respaldo no coincide con la conexión activa.
2. Siempre crea un **respaldo de seguridad del estado actual** (`-- MOTIVO: previo-a-restauracion`) ANTES de restaurar → la operación es reversible.
3. Restaurar exige confirmación escrita **RESTAURAR**: modal en la UI (botón deshabilitado hasta coincidir) o prompt interactivo en CLI (o flag `--confirmar`).
4. Antes de operar, tanto la UI como los comandos muestran SIEMPRE la conexión activa (driver/host/base) — transparencia del destino, regla de la sección 15.6.
5. Todos los eventos de restauración se registran en `storage/logs/laravel.log`.

### 16.3 Superficie del módulo

| Componente | Archivo | Notas |
|---|---|---|
| Rutas web | `routes/web.php` → `academico/respaldos*` (`academico.respaldos.*`) | index/store/descargar/restaurar/destroy |
| Controller | `app/Http/Controllers/RespaldoController.php` | Roles inline (convención del proyecto): ver/crear/descargar = Director+Subdirector; eliminar/restaurar = **solo Director** |
| Vista | `resources/views/academico/respaldos/index.blade.php` | Card de "Conexión activa", historial, modal de restauración (Alpine + SweetAlert2, scripts inline: el layout NO tiene `@stack('scripts')`) |
| Sidebar | `layouts/navigation.blade.php` | Enlace "Respaldos" en la sección Administración |
| Comando crear | `php artisan respaldos:crear --motivo=X` | Muestra conexión destino; SOLO lectura |
| Comando restaurar | `php artisan respaldos:restaurar {archivo}` | Doble confirmación; muestra metadatos del respaldo y destino |
| Schedule | `routes/console.php` → `respaldos:crear --motivo=automatico-diario` | Diario a `BACKUP_HORA` (02:00); requiere `schedule:work`, ya incluido en `composer dev` |
| Config | `config/respaldos.php` + variables `BACKUP_*` en `.env.example` | directorio, retención, hora, filas/insert, tablas excluidas |
| Tests | `tests/Feature/RespaldoServiceTest.php` | 6 pruebas sobre SQLite `:memory:`: ciclo completo crear→destruir→restaurar con Unicode/emojis/saltos de línea/apóstrofes/NULLs/booleans/decimales, tabla sin `id`, restauración repetible, retención, path traversal y rechazo de motor cruzado |

### 16.4 Cómo operar (procedimiento del colegio)

```bash
# Respaldar AHORA (solo lectura, seguro):
php artisan respaldos:crear --motivo="antes-de-actualizar"

# Listar respaldos disponibles: UI → /academico/respaldos (solo Dirección)

# RESTAURAR (destructivo; exige escribir RESTAURAR):
php artisan respaldos:restaurar respaldo_2026-10-21_055955.zip
```

**Reglas de oro:**
1. **Un respaldo que vive en el mismo disco que la BD no es un respaldo completo**: descargar el `.zip` y guardarlo en otro medio (USB/nube) con regularidad; la UI lo recuerda en su card de conexión.
2. Ejecutar los comandos SIEMPRE con el PHP de Laragon completo (`E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`), no el del PATH (el 8.5 de winget no tiene `pdo_pgsql` ni `zip` — misma lección del punto 6 de la sección 15.6).
3. Verificar SIEMPRE la conexión mostrada antes de restaurar (¿es Supabase? ¿es la base correcta?).
4. Primer respaldo real generado: `respaldo_2026-10-21_055955.zip` (44 tablas, 444 filas — estado post-reconstrucción del incidente).

### 16.5 Deuda / mejoras futuras del módulo

- **Copia externa automática** a Supabase Storage (o nube) usando la REST API + `service_role` key: hoy los respaldos solo viven en `storage/app/respaldos`. Prioridad alta.
- **Dump de ESTRUCTURA (DDL)**: el volcado cubre datos+DELETEs; el esquema se recupera con las migraciones (ya probado en el incidente). Si el esquema del respaldo difiere del actual, los INSERT con columnas inexistentes fallan y la restauración revierte entera (comportamiento seguro pero conviene saberlo).
- Excluir `sessions`/`cache` opcionalmente vía `BACKUP_EXCLUIR` si el volumen crece.

## 17. Conclusión arquitectónica

Este proyecto es un sistema escolar monolítico con una lógica de negocio altamente especializada y una capa de acceso bien organizada en torno a modelos académicos, roles y permisos. Tiene un diseño serio y un dominio muy definido, pero requiere disciplina para evitar que la lógica de autorización, validación y filtrado por contexto se disperse en controladores.

Desde la perspectiva de un desarrollador full stack, el punto más importante es entender que esta aplicación no es una CRUD genérica: es un sistema de operación escolar con reglas de negocio y permisos profundamente conectados con el modelo institucional. La clave del mantenimiento futuro es preservar esa coherencia en cada módulo: usuarios, matrículas, aulas, horarios, notas, asistencia y reportes.

Para un profesional de backend y frontend, el proyecto combina tres capas bien diferenciadas:

- dominio académico y persistencia
- seguridad y autorización institucional
- UI con Blade/Tailwind/Alpine para gestión operativa

Eso convierte a este repositorio en una base sólida, pero con una dependencia directa de la calidad del diseño de reglas y de la disciplina de mantenimiento del código.
