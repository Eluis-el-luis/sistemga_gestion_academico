# Contexto tecnico del Sistema de Gestion Academica

> Documento de incorporacion para desarrollo full stack. Describe el estado observado en el repositorio `sitema_gestion_academico` el 11 de septiembre de 2026. Las afirmaciones de implementacion son hechos observados en el codigo; las comprobaciones ejecutadas y sus limitaciones se separan explicitamente de los riesgos y recomendaciones.

## 1. Proposito y alcance

El proyecto es un sistema web monolitico para administrar la operacion academica de una institucion educativa. Centraliza identidad, usuarios, expediente de alumnos, matriculas, estructura escolar, horarios, evaluacion, asistencia, boletines, reportes y seguimiento institucional.

La implementacion actual es principalmente server-rendered:

- Backend: PHP, Laravel, Eloquent ORM, Blade y sesiones HTTP.
- Frontend: Blade, Tailwind CSS, Alpine.js y Vite.
- Persistencia: base de datos relacional mediante migraciones Laravel.
- Autorizacion: Laravel Policies, roles/permisos de Spatie y verificaciones puntuales en controladores.
- Integracion: no existe un API propio registrado en `routes/api.php`; Sanctum esta instalado, pero no constituye por si mismo un contrato API.

El repositorio contiene funcionalidades de las fases 5 a 8. La documentacion historica que lo limita a fase 6 no representa el estado actual.

## 2. Estado de auditoria

### 2.1 Inventario estructural

El inventario del repositorio contiene aproximadamente:

- 40 controladores, incluyendo autenticacion.
- 9 Form Requests de aplicacion y autenticacion.
- 32 modelos Eloquent.
- 13 Policies.
- 5 servicios de dominio.
- 56 migraciones.
- 14 seeders.
- 16 archivos de prueba.
- Vistas Blade de autenticacion, dashboard y modulos academicos.

Estos conteos describen archivos, no cobertura ni completitud funcional.

### 2.2 Verificaciones ejecutadas el 2026-09-11

| Comprobacion | Resultado | Alcance |
|---|---|---|
| Inspeccion de rutas, configuracion, controladores, requests, modelos, policies, servicios, migraciones, seeders, vistas y tests | Completada | Base de esta documentacion |
| `php artisan route:list --json` | Ejecutado | Confirma que las rutas se pueden cargar y muestra middlewares efectivos |
| `php artisan test` | No inicia | El PHP CLI no tiene `mbstring`, requerida por PHPUnit junto con otras extensiones |
| `npm run build` | No concluido | PowerShell bloqueo la ejecucion del script por politica de ejecucion; no prueba un fallo de Vite |
| `git status --short` | Cambio previo detectado | `app/Http/Controllers/DashboardController.php` ya estaba modificado; esta auditoria no lo cambio |
| Diagnostico estatico de VS Code | Sin errores reportados por el explorador | No sustituye tests runtime, migraciones ni build |
| `git diff --check -- Contexto.md` | Sin errores de espacios | El archivo queda sintacticamente valido como Markdown |

No debe afirmarse que toda la suite pasa ni que el build frontend es exitoso hasta repetir esas comprobaciones con el entorno correctamente configurado.

## 3. Stack y dependencias

### 3.1 Backend

`composer.json` declara:

- PHP `^8.3`.
- Laravel Framework `^13.8`.
- Laravel Sanctum `^4.3`.
- Laravel Tinker `^3.0`.
- Spatie Laravel Permission `^8.3`.

Dependencias de desarrollo relevantes:

- PHPUnit `^12.5.12`.
- Laravel Breeze `^2.4`.
- Laravel Pint `^1.27`.
- Laravel Pail, Collision, Faker y Mockery.

El autoload PSR-4 mapea `App\\` a `app/`, `Database\\Factories\\` a `database/factories/` y `Database\\Seeders\\` a `database/seeders/`.

### 3.2 Frontend

`package.json` declara:

- Vite `^8.0.0`.
- Tailwind CSS `^3.1.0`.
- `@tailwindcss/forms`, `@tailwindcss/postcss` y `@tailwindcss/vite`.
- Alpine.js `^3.16.1` en `dependencies` y otra declaracion `^3.4.2` en `devDependencies`.
- PostCSS, Autoprefixer, Concurrently y Laravel Vite Plugin.

SweetAlert2, Chart.js y algunas fuentes/iconos se cargan desde CDN en layouts o vistas; no son dependencias npm del proyecto.

### 3.3 Compatibilidad

Laravel requiere PHP 8.3 o superior. PHPUnit requiere, como minimo, `dom`, `filter`, `json`, `libxml`, `mbstring`, `tokenizer` y `xmlwriter`, ademas de las extensiones PDO del motor seleccionado.

Desarrollo, CI y produccion deben fijar una matriz con version exacta de PHP, extensiones, Node/npm y motor de base de datos. La ausencia de `mbstring` en el CLI actual impide ejecutar pruebas aunque el codigo pueda cargar correctamente.

## 4. Arranque y arquitectura

### 4.1 Bootstrap

`bootstrap/app.php` configura una aplicacion Laravel moderna con:

- rutas web desde `routes/web.php`;
- comandos desde `routes/console.php`;
- health check `/up`;
- respuestas JSON forzadas solamente para solicitudes con path `api/*`.

No se registran middlewares adicionales en el bootstrap. La proteccion de negocio se aplica en las rutas y en controladores/policies.

### 4.2 Capas

La separacion vigente es:

1. Rutas: URL, verbo, nombre, controlador y middleware.
2. Controladores: coordinacion de request, autorizacion, consultas, transacciones y respuestas.
3. Form Requests: validacion de flujos que ya los utilizan.
4. Policies: autorizacion de modelos y relaciones de dominio.
5. Servicios: reglas reutilizables de notas, asistencia, reportes, reparacion y aulas.
6. Modelos Eloquent: tablas, relaciones, casts, fillable y soft deletes.
7. Migraciones: esquema, claves foraneas, indices y restricciones.
8. Vistas Blade, JavaScript y CSS: interfaz y comportamiento local.

No existe una capa de repositorios, bus de comandos ni SPA. Los cambios deben respetar estas fronteras y no crear abstracciones nuevas sin necesidad.

### 4.3 Scripts operativos

`composer run setup` ejecuta instalacion, copia `.env`, genera clave, migra, instala npm y compila assets.

`composer run dev` intenta levantar en paralelo:

- servidor PHP con `php artisan serve`;
- listener de cola con `php artisan queue:listen`;
- visor de logs con `php artisan pail`;
- Vite con `npm run dev`.

Depende de `npx concurrently` y de que PowerShell permita ejecutar scripts npm/npx.

## 5. Configuracion de entorno

`.env.example` configura por defecto:

| Area | Valor |
|---|---|
| Entorno | `local` |
| Debug | `true` |
| URL | `http://localhost` |
| Locale | `en` |
| Base de datos | SQLite |
| Sesiones | `database` |
| Cache | `database` |
| Cola | `database` |
| Filesystem | `local` |
| Mail | `log` |
| Broadcasting | `log` |
| Redis | Configurado, no seleccionado por defecto |

`config/database.php` contiene conexiones SQLite, MySQL, MariaDB, PostgreSQL y SQL Server. Esto no significa que las migraciones esten certificadas para todos ellos; el motor objetivo debe elegirse y probarse desde cero.

Antes de produccion se deben fijar `APP_ENV`, `APP_DEBUG=false`, `APP_KEY`, `APP_URL`, credenciales de base de datos, drivers de sesion/cache/cola, almacenamiento persistente, mailer real, retencion de logs y backups.

## 6. Identidad, autenticacion y perfil

### 6.1 Modelo autenticable

El guard `web` utiliza sesiones y el provider `users` usa `App\\Models\\Usuario`. `Usuario` representa la tabla singular `usuario` y combina:

- autenticacion Laravel;
- `HasApiTokens` de Sanctum;
- `HasRoles` de Spatie;
- notificaciones;
- soft deletes;
- relacion con `Rol` mediante `rol_id`;
- relaciones opcionales con `Docente` y `Alumno`.

`HasApiTokens` no implica que exista un flujo de tokens para clientes: no hay rutas API propias.

### 6.2 Autenticacion

`routes/auth.php` implementa:

- login y logout;
- recuperacion y restablecimiento de contrasena;
- verificacion y reenvio de correo;
- confirmacion de contrasena;
- cambio de contrasena.

El registro publico no esta declarado en las rutas actuales, aunque permanecen `RegisteredUserController` y una vista heredada de Breeze. Ese residuo debe eliminarse o documentarse como flujo administrativo no expuesto.

### 6.3 Perfil

`ProfileController` y `ProfileUpdateRequest` gestionan el perfil autenticado. El proyecto usa `nombre_completo`, no el atributo generico `name` del skeleton inicial. Cualquier cambio debe revisar config, modelo, factory, request, vistas y tests.

## 7. Autorizacion y roles

### 7.1 Mecanismos activos

La autorizacion se reparte entre:

1. `$this->authorize(...)` y Policies.
2. `hasRole`, `hasAnyRole` y `hasPermissionTo` de Spatie.
3. Condiciones directas e `abort(403)` en controladores.
4. `authorize(): true` en varios Form Requests, por lo que esos requests no son la fuente principal de autorizacion.

Todas las rutas academicas tienen `auth`, pero no existe un middleware global que traduzca automaticamente cada permiso a una ruta. La seguridad efectiva depende de cada controlador y policy.

### 7.2 Dos representaciones de rol

El sistema mantiene dos conceptos:

- catalogo de negocio `rol`, referenciado por `usuario.rol_id`;
- roles/permisos Spatie en `roles`, `permissions`, `model_has_roles`, `model_has_permissions` y `role_has_permissions`.

`PermisoSeeder` crea roles Spatie como `Director`, `Subdirector`, `Docente Guia`, `Docente por Asignatura`, `Alumno`, `Coordinador`, `Gestor de Usuarios` y `Secretaria`. Tambien crea permisos como `alumnos.gestionar`, `notas.gestionar`, `asistencia.gestionar`, `boletines.ver`, `malla.gestionar`, `reportes.ver` y `configuracion.gestionar`.

No existe un servicio unico que sincronice `rol_id` y el rol Spatie dentro de una transaccion. Es una deuda de arquitectura antes de ampliar la matriz de acceso.

### 7.3 Policies

Existen policies para alumnos, aulas, asignaciones aula-asignatura-docente, asistencia de aula, apoyo de padres, avance de contenidos, boletines, examen de reparacion, horarios, matriculas, malla curricular, notas y usuarios.

Varias conceden acceso general a Director y Subdirector mediante `before()`. Una prueba basada solo en permisos individuales no representa necesariamente ese comportamiento.

### 7.4 Riesgos de autorizacion

- `NotaController::index()` decide visibilidad por rol sin una invocacion evidente a una policy.
- `GestorBoletinController` conserva autorizacion comentada y debe auditarse antes de considerarlo exclusivo.
- Una ruta nueva con solo `auth` puede quedar accesible a todo usuario autenticado si el controlador no autoriza.
- Reportes contienen datos sensibles de alumnos, familias, notas y asistencia; deben mantenerse restringidos por rol y contexto.
- El nombre Spatie es `Docente Guia` sin tilde; no introducir variantes sin migracion y pruebas.

## 8. Modelo de dominio

### 8.1 Catalogos y estructura escolar

- `Rol`: catalogo propio de roles.
- `Modalidad`: modalidad o jornada academica.
- `AnioEscolar`: ciclo escolar y bandera de activo.
- `Grado`: grado asociado a modalidad y limite de horas maximas.
- `Asignatura`: materia, incluyendo area.
- `GrupoMateria`: agrupacion usada por el boletin oficial.
- `CorteEvaluativo`: periodo de evaluacion asociado al año escolar y con pesos configurables.
- `IndicadorLogro`: catalogo cualitativo asociado a modalidad y rangos de grado.
- `MallaCurricular`: relacion grado-asignatura y configuracion curricular.
- `BloqueHorario`: bloque de jornada, turno y bandera `es_recreo`.

### 8.2 Personas y organizacion

- `Usuario`: identidad, credenciales, roles y borrado logico.
- `Docente`: perfil docente asociado a usuario y opcionalmente a modalidad coordinada.
- `Alumno`: expediente personal, familiar, medico y autorizados.
- `Aula`: grupo/seccion asociado a grado, modalidad, año escolar y docente guia.
- `Matricula`: vinculacion alumno-aula-año, con estado operativo y soft delete.

### 8.3 Asignaciones y horarios

`AulaAsignaturaDocente` es la entidad central que indica que una asignatura se imparte en un aula durante un año, con docente opcional, horas semanales y estado activo.

`Horario` relaciona una asignacion con un `BloqueHorario`. Los controladores validan, segun el flujo, modalidad, jornada, recreos y colisiones. `AulaService` interviene en creacion/configuracion de aulas y puede sincronizar asignaturas desde la malla.

### 8.4 Evaluacion

- `ActividadEvaluativa`: actividad de una asignacion y corte; puede tener tipo.
- `NotaActividad`: calificacion de una matricula para una actividad y usuario que actualiza.
- `Nota`: calificacion consolidada por matricula, asignacion, corte e indicador.
- `CorteCerrado`: bloqueo/auditoria de cortes.
- `SolicitudEdicionNota`: solicitud y resolucion de desbloqueos.
- `Boletin`: consolidado oficial por matricula.
- `ExamenReparacion`: examen extraordinario por matricula y asignatura.

### 8.5 Asistencia y seguimiento

- `AsistenciaAula`: asistencia diaria por matricula gestionada por docente guia.
- `AsistenciaAsignatura`: incidencia por matricula, asignatura, bloque y fecha.
- `AsistenciaPersonal`: marcacion de llegada del personal autenticado.
- `SustitucionDocente`: tabla de sustituciones; existe migracion, pero no se identifico superficie completa de modelo/controlador/ruta.
- `AvanceContenido`: porcentaje por asignacion y periodo.
- `ApoyoPadres`: participacion familiar por aula y mes.
- `EvaluacionFamiliar`: evaluacion/seguimiento familiar.
- `IncidenciaDisciplinaria`: control disciplinario asociado al flujo de alumno/matricula.

## 9. Persistencia y esquema

### 9.1 Convenciones

Las tablas del dominio usan nombres singulares en español: `usuario`, `alumno`, `docente`, `aula`, `matricula`, `asignatura`, `nota` y `horario`, entre otras. Los modelos declaran `$table` y claves explicitas cuando Laravel no puede inferirlas.

No cambiar una tabla a plural ni renombrar una FK suponiendo que Eloquent la resolvera. Cada cambio debe revisar modelo, relaciones, bindings, policies, factories, seeders, vistas y tests.

### 9.2 Integridad y eliminacion

Las migraciones combinan `cascade`, `restrict`, `set null` y soft deletes. Estas decisiones son reglas de negocio. El estado de matricula (`activo`, `retirado`, `repitente`, `promovido`) no equivale a `deleted_at`; las consultas deben expresar ambas dimensiones.

### 9.3 Restricciones e idempotencia

Hay restricciones unicas para notas, asistencia y otras entidades. Los servicios usan `updateOrCreate` en flujos idempotentes.

La unicidad debe protegerse en aplicacion para mensajes claros y en base de datos para soportar concurrencia. Las operaciones por lote de notas/asistencia deben permanecer en transacciones.

### 9.4 Migraciones que requieren atencion

- La migracion de `apoyo_padres` crea `apoyo_padres`, pero su `down()` referencia `apoyo_padre`; debe corregirse para rollback limpio.
- Existen migraciones de `sustitucion_docente` y `envio_boletines` sin superficie funcional completa identificada.
- La migracion que agrega `modalidad_id` a `docente` debe alinearse con `$fillable` y los formularios de Docente.
- Las migraciones se agregaron por fases; se debe probar `migrate:fresh` en el motor objetivo, no solo una base ya migrada.

## 10. Contrato HTTP

### 10.1 Acceso y autenticacion

`GET /` redirige a `login`. Las rutas de autenticacion cubren `/login`, `/forgot-password`, `/reset-password`, `/verify-email`, `/confirm-password`, `/password` y `/logout`.

El dashboard y avisos estan en `/dashboard` y requieren autenticacion. El perfil esta en `/profile` y requiere autenticacion.

### 10.2 Area academica

Todas las rutas de negocio estan bajo prefijo `/academico`, nombres `academico.*` y middleware `auth`.

| Modulo | Superficie principal |
|---|---|
| Alumnos | resource `academico.alumnos.*` |
| Matriculas | resource, `retirar`, `reactivar` |
| Prematricula | listado, promover y remitir |
| Aulas | resource, asignaciones, asignaturas y horarios |
| Malla | CRUD parcial, clonado y horas por grado |
| Bloques | CRUD parcial, clonado, generacion masiva y eliminacion de jornada |
| Usuarios | resource y reset de contraseña |
| Horarios | alta/baja por aula y visor por docente/aula |
| Notas | indice, planilla, guardado, cierre y desbloqueo |
| Actividades | alta, actualizacion y baja por asignacion |
| Cortes | consulta y actualizacion |
| Asistencia | aula, asignatura y personal |
| Boletines | listado, detalle, constancia, aprobacion y bandeja |
| Reparacion | listado, alta y baja |
| Avance | listado, alta y baja |
| Apoyo familiar | apoyo de padres y evaluacion familiar |
| Disciplina | listado, alta y actualizacion |
| Reportes | notas, asistencia, rendimiento, MINED, estudiantes y padres |
| Grupo materia | CRUD y asignacion de materias |

### 10.3 Duplicaciones y bindings

`routes/web.php` declara dos veces `PUT academico/usuarios/{usuario}/reset-password` con el mismo nombre y accion. `route:list` muestra una ruta efectiva, pero la declaracion duplicada debe eliminarse.

Los bindings `{alumno}`, `{matricula}`, `{aula}`, `{asignacion}`, `{horario}`, `{bloque}`, `{corte}`, `{usuario}` e `{incidencia}` resuelven IDs, pero no sustituyen la comprobacion de pertenencia al aula, año, docente o asignacion.

## 11. Flujos de negocio

### 11.1 Alumno, expediente y matricula

`StoreAlumnoRequest` y `UpdateAlumnoRequest` validan datos del expediente. `AlumnoPolicy` controla consulta y gestion.

`MatriculaController` crea y actualiza matriculas y expone retiro/reactivacion. `PrematriculaController` gestiona promocion y remision. Las transiciones deben ser coherentes con `deleted_at`, aula destino, año escolar y duplicidad.

### 11.2 Aula y malla

La malla relaciona grados y asignaturas. Al crear/configurar aulas, `AulaService` puede sincronizar la estructura curricular y asignaciones base.

Las asignaciones deben filtrarse por aula, modalidad, año y estado `activo`. No se debe aceptar un `asignacion_id` aislado sin validar el contexto.

### 11.3 Bloques y horarios

`BloqueHorarioController` administra bloques por modalidad, clonacion, generacion masiva y eliminacion de jornadas. Un bloque puede marcarse como recreo.

`HorarioController` crea y elimina horarios de un aula. `VisorHorarioController` consulta horarios por aula y docente. Antes de persistir deben comprobarse colisiones de bloque, docente, aula, año y asignacion.

### 11.4 Calificaciones

El flujo incluye:

1. Seleccion de asignacion y corte.
2. Gestion de actividades evaluativas.
3. Registro de `NotaActividad`.
4. Calculo/consolidacion de `Nota`.
5. Indicador cualitativo y promedios mediante `NotaService`.
6. Cierre mediante `CorteCerrado`.
7. Solicitud y resolucion de desbloqueo mediante `SolicitudEdicionNota`.

`NotaService` observa estos rangos:

| Rango | Indicador |
|---:|---|
| 90-100 | `AA` |
| 76-89 | `AS` |
| 60-75 | `AF` |
| 0-59 | `AI` |

La aprobacion comienza en 60 y el servicio calcula promedios semestrales/anuales con redondeo segun la implementacion vigente.

**Riesgo importante:** la ruta de guardado usa `NotaController::store(Request $request, ...)`, no `StoreNotaRequest`; las reglas de ese request no se aplican automaticamente. Deben validarse rango, matricula, pertenencia al aula, asignacion, corte y año escolar, idealmente aplicando efectivamente un Form Request.

El cierre tampoco debe aceptar `corte_evaluativo_id` sin comprobar existencia y pertenencia al año escolar de la asignacion.

### 11.5 Asistencia

**Asistencia de aula:** el docente guia consulta estudiantes activos y registra asistencia diaria. `GuardarAsistenciaAulaRequest` valida existencia de matricula, pero el controlador/servicio debe comprobar pertenencia al aula.

**Asistencia por asignatura:** la clave funcional es matricula + asignatura + bloque + fecha. Las incidencias observadas son `Fuga`, `Llegada Tardía` y `Permiso de Salida`. Una fila ausente puede representar presencia; no asumir que `Presente` es siempre persistido sin revisar migracion y controlador.

El acceso debe comprobar que el docente pertenece a la asignacion o que su rol permite supervision. El borrado de incidencia debe volver a comprobar esa pertenencia y no confiar solo en binding.

**Asistencia personal:** `AsistenciaPersonalController` permite consultar y marcar llegada mediante `/asistencia-personal/marcar`.

### 11.6 Boletines y reparacion

`BoletinController` consulta matriculas/notas, genera detalle y constancia, y permite aprobar boletines. Debe comprobar notas completas y ciclo correcto.

`GestorBoletinController` ofrece la bandeja de impresion. Su autorizacion requiere auditoria porque hay codigo comentado relacionado con acceso.

`ExamenReparacionController` usa `ReparacionService`. El resultado es `aprobado` desde 60 y `reprobado` por debajo. El servicio es idempotente por matricula/asignatura y actualiza el registro existente.

### 11.7 Avance, familia y disciplina

`AvanceContenidoController` registra porcentajes por asignacion y mes. Debe validar rango y pertenencia del usuario al contexto.

`ApoyoPadresController` registra por aula y mes la cantidad de padres que apoyan frente al total. Rechaza `cantidad_apoyan > total_padres` y es idempotente por aula/mes.

`ApoyoFamiliarController` gestiona evaluaciones familiares. `IncidenciaDisciplinariaController` lista, crea y actualiza incidencias.

### 11.8 Reportes

`ReporteController` y `ReporteService` concentran consultas de:

- control, pendientes y notas globales;
- notas por asignatura;
- rendimiento por corte e historial del estudiante;
- asistencia global, por seccion/dia, por rango y por estudiante;
- estadisticas de asistencia;
- reportes MINED, de estudiantes y de padres.

Son consultas de alto impacto en privacidad. Cada filtro debe limitar por año escolar, modalidad, aula y estado de matricula cuando corresponda. La prueba existente verifica acceso de Director y rechazo del docente por asignatura al centro de reportes.

## 12. Seeders y datos iniciales

`DatabaseSeeder` ejecuta, en orden general:

1. `RolSeeder`.
2. `PermisoSeeder`.
3. Modalidades y años escolares.
4. Grados, asignaturas y grupos de materias.
5. Malla e indicadores.
6. Cortes y bloques.
7. `UsuarioSeeder`.

No se observan seeders de docentes, alumnos, aulas, matriculas, asignaciones u horarios operativos. Una instalacion limpia puede tener catalogos y cuentas, pero no una estructura escolar completa.

`UsuarioSeeder` contiene contraseñas iniciales conocidas para desarrollo. No ejecutar esos datos en produccion; usar bootstrap seguro y rotacion de credenciales.

Los tests crean con frecuencia roles Spatie y catalogo `Rol` por separado. Eso puede ocultar problemas de sincronizacion que aparecerian en una base real.

## 13. Frontend

### 13.1 Renderizado y estado local

El layout autenticado esta en `resources/views/layouts/app.blade.php`; las vistas de negocio estan bajo `resources/views/academico`. Blade genera HTML en servidor y Vite procesa `resources/js/app.js` y `resources/css/app.css`.

`resources/js/app.js` registra Alpine globalmente y ejecuta `Alpine.start()`. El layout usa Alpine para sidebar, persistencia en `localStorage` y comportamiento responsive.

### 13.2 Estilos y dependencias

`resources/css/app.css` importa base, componentes y utilidades Tailwind. `tailwind.config.js` escanea vistas y configura modo oscuro por clase.

La interfaz usa direccion institucional clara, acentos ambar/amarillo, tonos slate y Figtree desde fuente externa. Existen SVG inline repetidos.

Alpine se carga desde Vite y tambien aparece en CDN en algunas vistas. SweetAlert2, Chart.js, fuentes e iconos externos introducen dependencia de disponibilidad, CSP, cache, privacidad y reproducibilidad. Para produccion debe definirse una estrategia de versionado, integridad y fallback.

## 14. Pruebas y calidad

### 14.1 Pruebas existentes

La suite visible incluye:

- autenticacion, logout, recuperacion, cambio de contraseña y verificacion;
- perfil;
- matriz de permisos y autorizacion por rol;
- idempotencia;
- `NotaService`;
- fase 7: avance, reparacion y apoyo de padres;
- reportes.

### 14.2 Cobertura faltante o a reforzar

- aislamiento por docente, aula, modalidad y año;
- propiedad contextual de notas, asistencia, horarios y boletines;
- cierre y desbloqueo de cortes;
- transiciones de matricula y prematricula;
- permisos de Secretaria, Coordinador y Gestor;
- disciplina, asistencia personal, grupos y evaluacion familiar;
- rollback de migraciones;
- concurrencia contra indices unicos;
- privacidad de reportes;
- exportacion de constancias y boletines.

Los limites de `NotaService` deben cubrir 0, 59, 60, 75, 76, 89, 90 y 100, mas negativos, superiores a 100, nulos y decimales conforme al negocio.

## 15. Riesgos priorizados

### Criticos antes de produccion

1. Habilitar y fijar extensiones PHP, especialmente `mbstring`, y repetir PHPUnit en CI.
2. Aplicar realmente `StoreNotaRequest` o validacion equivalente al guardado.
3. Garantizar pertenencia contextual de matriculas, asignaciones, cortes, aulas e incidencias antes de leer o mutar.
4. Auditar autorizacion de `GestorBoletinController` y endpoints que dependen de comprobaciones internas.
5. Elegir fuente canonica de rol o implementar sincronizacion transaccional entre `rol_id` y Spatie.
6. Probar migraciones desde cero en el motor de produccion.

### Altos

1. Corregir el `down()` de `apoyo_padres`.
2. Eliminar la declaracion duplicada de `usuarios/{usuario}/reset-password`.
3. Proteger las contraseñas de `UsuarioSeeder`.
4. Revisar `Docente::$fillable` frente a `modalidad_id`.
5. Añadir indices compuestos cuando consultas y reglas lo requieran.
6. Evitar mezclar registros activos y soft-deleted en reportes, matriculas y boletines.

### Medios

1. Eliminar o consolidar Alpine CDN/Vite.
2. Decidir estrategia para Chart.js, SweetAlert2 y fuentes CDN.
3. Completar o retirar superficies de sustitucion y envio de boletines.
4. Sustituir comentarios heredados y README generico de Laravel.
5. Incorporar pruebas de fases 7/8 y de los cambios pendientes del dashboard.

## 16. Guia para implementar cambios

1. Identificar modelo, migracion y policy dueños del dato.
2. Revisar contexto: año, modalidad, aula, asignacion, matricula y estado.
3. Validar entrada mediante Form Request realmente aplicado a la ruta.
4. Autorizar antes de consultar o mutar datos sensibles.
5. Preferir policy o servicio reutilizable a condicion inline.
6. Mantener transacciones en operaciones por lote e indices unicos para idempotencia.
7. Preservar nombres de tablas y claves explicitas en español.
8. Añadir prueba feature del endpoint y prueba unitaria de la regla.
9. Ejecutar migraciones limpias, tests, rutas y build frontend en el entorno objetivo.
10. Limpiar cache de configuracion y permisos tras cambiar seeders.
11. Revisar privacidad cuando una consulta devuelve alumnos, notas, asistencia, familias o disciplina.
12. No introducir API por asumir que Sanctum ya define un contrato.

## 17. Comandos operativos

### Instalacion

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Desarrollo

```powershell
composer run dev
```

Como alternativa, ejecutar servidor Laravel, Vite, cola y logs por separado para aislar fallos.

### Diagnostico

```powershell
php artisan about
php artisan route:list
php artisan migrate:status
php artisan optimize:clear
php artisan view:cache
php artisan permission:cache-reset
```

### Pruebas y calidad

```powershell
php artisan test
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
vendor/bin/pint --test
npm run build
```

En Windows, si `npm run build` es bloqueado por PowerShell, revisar la politica de ejecucion del usuario o ejecutar desde un shell autorizado. La solucion debe quedar documentada en CI, no depender de una configuracion manual local.

## 18. Referencia de archivos

| Responsabilidad | Archivos |
|---|---|
| Bootstrap | `bootstrap/app.php` |
| Rutas | `routes/web.php`, `routes/auth.php` |
| Dependencias | `composer.json`, `composer.lock`, `package.json`, `package-lock.json` |
| Entorno | `.env.example`, `config/database.php`, `config/auth.php`, `config/permission.php` |
| Identidad | `app/Models/Usuario.php`, `app/Http/Controllers/Auth/`, `ProfileController.php` |
| Permisos | `database/seeders/PermisoSeeder.php`, `app/Policies/`, `config/permission.php` |
| Academico | `app/Models/`, `app/Http/Controllers/`, `app/Http/Requests/` |
| Reglas | `app/Services/NotaService.php`, `AsistenciaService.php`, `AulaService.php`, `ReporteService.php`, `ReparacionService.php` |
| Esquema | `database/migrations/` |
| Datos iniciales | `database/seeders/` |
| UI | `resources/views/`, `resources/js/app.js`, `resources/css/app.css`, `tailwind.config.js`, `vite.config.js` |
| Tests | `tests/Feature/`, `tests/Unit/`, `phpunit.xml` |

## 19. Conclusiones

La base actual ya es un sistema academico amplio, no un skeleton de Laravel: cubre estructura escolar, matricula, evaluacion, asistencia, boletines, reportes y seguimiento institucional. La separacion entre controladores, requests, policies, servicios, modelos y vistas permite continuar, pero la seguridad depende de mantener esas fronteras.

Las prioridades tecnicas son cerrar la validacion contextual de notas y asistencia, unificar el modelo de roles, completar autorizacion de boletines/reportes, probar migraciones limpias y hacer reproducible el entorno PHP/Node. Las futuras actualizaciones de este documento deben conservar la separacion entre capacidades observadas, verificaciones ejecutadas y trabajo pendiente.
