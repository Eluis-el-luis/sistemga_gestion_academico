# Contexto tecnico del Sistema de Gestion Academica

> Documento de incorporacion para desarrolladores. Describe el estado observado en el repositorio `sitema_gestion_academico` sobre la rama `feature/fase-6-asistencia`. Las afirmaciones de estado se basan en el codigo existente y en comprobaciones ejecutadas el 26 de agosto de 2026; las recomendaciones estan marcadas como tales.

## 1. Resumen ejecutivo

El proyecto es una aplicacion web monolitica Laravel para administrar la operacion academica de un colegio. La interfaz se renderiza principalmente con Blade y Tailwind CSS; Alpine.js aporta interacciones locales. El backend usa Eloquent sobre un esquema relacional con tablas en espanol y nombres singulares, autenticacion de sesion y autorizacion combinada mediante Laravel Policies y `spatie/laravel-permission`.

Los modulos funcionales identificables son:

- autenticacion, verificacion de correo, recuperacion de contrasena y perfil;
- tablero con avisos y contadores de alumnos/docentes;
- usuarios, roles y perfiles de docente/alumno;
- catalogos de modalidad, grado, asignatura, ano escolar y cortes evaluativos;
- expediente y matricula de alumnos;
- aulas, docente guia y asignaciones aula-asignatura-docente;
- malla curricular;
- bloques y horarios;
- calificaciones, actividades evaluativas e indicadores de logro;
- asistencia por aula y asistencia por asignatura;
- visor de horarios por docente y por aula.

La rama refleja una evolucion incremental por fases: calificaciones (fase 5), asistencia (fase 6), bloques horarios y campos complementarios de alumno. Hay codigo funcional, pero tambien restos del skeleton de Laravel/Breeze y varias decisiones de seguridad o consistencia que conviene resolver antes de considerar el sistema listo para produccion.

## 2. Estado del entorno verificado

### Versiones y dependencias

`composer.json` declara:

- PHP `^8.3`.
- Laravel Framework `^13.8`.
- Laravel Sanctum `^4.3`.
- Laravel Tinker `^3.0`.
- Spatie Laravel Permission `^8.3`.
- PHPUnit `^12.5.12`, Laravel Breeze `^2.4`, Pint y Collision en desarrollo.

En la maquina de trabajo, `php -v` reporta PHP 8.5.8 CLI.

El frontend declara Vite 8, Tailwind 3.1, los plugins de Tailwind/Vite, Alpine.js 3.16.1, PostCSS, Autoprefixer y Concurrently. Hay una declaracion duplicada de Alpine en `devDependencies` y `dependencies`; debe consolidarse para evitar ambiguedad de instalacion.

### Comprobaciones realizadas

- `php artisan route:list`: carga correctamente y muestra 91 rutas. La opcion `--compact` no existe en esta version, por lo que la comprobacion se ejecuto sin ella.
- `php artisan test`: no inicia porque la extension PHP `mbstring` no esta habilitada. PHPUnit tambien enumera extensiones requeridas del runtime (`dom`, `filter`, `json`, `libxml`, `mbstring`, `tokenizer`, `xmlwriter`); el error observado es especificamente `mb_strimwidth()` indefinida.
- `npm run build`: no se pudo ejecutar porque `npm` no esta disponible en el PATH de la terminal actual.
- `php artisan about`: no pudo completar por la misma ausencia de `mbstring`, ya que Termwind llama a `mb_strimwidth()`.

Estas limitaciones afectan la validacion del entorno, no constituyen por si mismas un diagnostico de fallo del codigo de negocio. Para una verificacion completa hay que usar el PHP de Laragon con `mbstring` habilitado y una instalacion Node/npm disponible.

## 3. Arquitectura y puntos de entrada

La aplicacion sigue la estructura estandar de Laravel:

- `bootstrap/app.php`: configura la aplicacion, registra `routes/web.php` y `routes/console.php`, expone `/up` como health check y fuerza respuestas JSON para `api/*`.
- `routes/web.php`: contrato HTTP principal. El dominio academico esta bajo middleware `auth`, prefijo `/academico` y nombres `academico.*`.
- `routes/auth.php`: rutas Breeze para registro, login, verificacion, recuperacion de contrasena, confirmacion de contrasena, cambio de contrasena y logout.
- `app/Http/Controllers`: orquesta solicitudes, consultas, validacion y respuestas Blade.
- `app/Http/Requests`: concentra parte de la validacion/autorizacion de alumnos, aulas, matriculas, notas y asistencia de aula.
- `app/Models`: modelos Eloquent con `$table` explicita para las tablas singulares en espanol.
- `app/Policies`: reglas de autorizacion de modelo y de permisos.
- `app/Services`: logica de dominio reutilizable; el servicio mas visible es `NotaService`.
- `resources/views`: interfaz Blade; hay 61 plantillas Blade contabilizadas.
- `resources/js/app.js`: inicializa Alpine.js.
- `resources/css/app.css`: importa las capas base, componentes y utilidades de Tailwind.
- `public/build`: assets compilados existentes en el repositorio/entorno, aunque deben regenerarse mediante Vite durante despliegue.

No se observa una API propia en `routes/api.php`; Sanctum esta instalado, pero el flujo expuesto actualmente es web con sesion. No debe asumirse que Sanctum implique que exista una API lista para consumo.

## 4. Rutas y contrato HTTP

### Publicas y autenticacion

`/` retorna la vista `welcome`. Las rutas de autenticacion incluyen `/login`, `/register`, `/forgot-password`, `/reset-password`, `/verify-email`, `/confirm-password`, `/password` y `/logout`.

El dashboard y la gestion de avisos exigen `auth` y `verified`:

- `GET /dashboard`
- `POST /dashboard/avisos`
- `PUT /dashboard/avisos/{id}`
- `DELETE /dashboard/avisos/{id}`

El perfil exige solamente `auth`.

### Modulo academico

Las rutas estan agrupadas bajo `Route::middleware('auth')` y `prefix('academico')->name('academico.')`.

| Area | Rutas/controladores principales |
|---|---|
| Alumnos | `Route::resource('alumnos', AlumnoController::class)` |
| Matriculas | resource mas `retirar` y `reactivar` |
| Aulas | resource, asignaciones, detalle, asignaturas y horarios |
| Malla | index/store/destroy y actualizacion de horas por grado |
| Bloques | index/store/destroy |
| Horarios | alta/consulta/baja por aula; gestor y visor de horarios |
| Usuarios | resource y reset de contrasena |
| Notas | listado, planilla y almacenamiento por lote |
| Actividades | listado, alta y baja por asignacion |
| Cortes | listado y actualizacion |
| Asistencia | aula y asignatura, incluyendo borrado de incidencia |

Hay dos declaraciones identicas para `GET academico/gestor-horarios`, ambas con nombre `academico.gestor-horarios.index`. `route:list` muestra una ruta efectiva, pero la duplicacion debe eliminarse para evitar comportamiento dependiente del orden y ruido de mantenimiento.

El uso de route model binding es amplio: `{alumno}`, `{matricula}`, `{aula}`, `{grado}`, `{asignacion}`, `{horario}`, `{bloque}`, `{corte}`, `{usuario}` e `{incidencia}` se resuelven contra modelos Eloquent. Al cambiar nombres de tabla, claves o scope bindings hay que revisar simultaneamente rutas, firmas de controlador y policies.

## 5. Modelo de dominio y persistencia

### Convenciones de datos

El dominio academico usa tablas singulares: `usuario`, `alumno`, `docente`, `aula`, `matricula`, `asignatura`, `grado`, `modalidad`, `anio_escolar`, `aula_asignatura_docente`, `horario`, `nota`, etc. Por eso los modelos declaran `$table` explicitamente. Las relaciones tambien pasan claves explicitas cuando no coinciden con la convencion plural de Laravel.

Las migraciones usan claves `foreignId`, indices unicos y acciones de borrado variadas: `cascade`, `restrict`, `set null` y `nullOnDelete`. Esta semantica es parte del negocio y debe preservarse en cambios de esquema.

### Entidades y relaciones esenciales

- `Usuario` es el modelo autenticable y usa la tabla `usuario`. Tiene `HasApiTokens`, `HasRoles`, `Notifiable` y `SoftDeletes`. Tiene un `hasOne` hacia `Docente` y otro hacia `Alumno`.
- `Alumno` pertenece opcionalmente a `Usuario`, tiene muchos `Matricula` y conserva expediente familiar, datos de salud, autorizados y compromiso cristiano.
- `Docente` pertenece a `Usuario` y opcionalmente a una modalidad coordinada; tiene muchas aulas guiadas.
- `Aula` pertenece a grado, modalidad, ano escolar y docente guia; tiene muchas matriculas.
- `Matricula` vincula alumno, aula y ano escolar; tiene muchas notas. Admite estados operativos como `activo` y retiro/reactivacion.
- `AulaAsignaturaDocente` es la asignacion central: aula, asignatura, docente opcional, ano escolar y horas semanales. Tiene muchos horarios.
- `Horario` pertenece a una asignacion y a un `BloqueHorario`.
- `MallaCurricular` vincula grado y asignatura.
- `CorteEvaluativo` pertenece a un ano escolar.
- `Nota` vincula matricula, asignacion, corte e indicador de logro.
- `ActividadEvaluativa` pertenece a una asignacion y corte, y tiene calificaciones.
- `CalificacionActividad` vincula actividad y matricula.
- `AsistenciaAula` vincula matricula con la asistencia del docente guia.
- `AsistenciaAsignatura` vincula matricula, asignatura, bloque y fecha para incidencias por clase.
- `BloqueHorario` pertenece a modalidad y distingue recreos con `es_recreo`.
- `IndicadorLogro` se asocia a modalidad y rangos de grado.

### Migraciones en orden funcional

1. Infraestructura Laravel: cache.
2. Catalogos: rol, modalidad, ano escolar, grado, corte evaluativo y asignatura.
3. Identidad: usuario, docente y alumno.
4. Organizacion escolar: aula, matricula y asignacion aula-asignatura-docente.
5. Horarios y evaluacion: horario, notas, indicadores, malla y tablas relacionadas.
6. Seguridad: permisos de Spatie y tokens personales.
7. Evolucion: campos de expediente del alumno, bloques horarios, soft deletes en tablas core, asistencia por asignatura y horas maximas por grado.

`database/seeders/DatabaseSeeder.php` respeta esta dependencia general: primero catalogos y permisos, despues usuarios/perfiles y finalmente aulas, matriculas, asignaciones y horarios.

### Asistencia por asignatura

La tabla `asistencia_asignatura` tiene una restriccion unica sobre `matricula_id`, `asignatura_id`, `bloque_horario_id` y `fecha`. El controlador usa `updateOrCreate` con exactamente esas columnas, por lo que el contrato es una incidencia por estudiante, materia, bloque y dia. Los estados aceptados por la solicitud son `Fuga`, `Llegada Tardía` y `Permiso de Salida`; la migracion menciona tambien `Presente`, pero el controlador no lo acepta como entrada. Esa diferencia debe resolverse explicitamente si `Presente` sigue siendo un valor de catalogo o si la ausencia de fila representa presencia.

## 6. Flujos de negocio relevantes

### Calificaciones

`NotaController` recibe una planilla validada, verifica la asignacion y autoriza la habilidad `calificar`. Dentro de una transaccion recorre las notas, calcula el codigo cualitativo mediante `NotaService`, busca el indicador y ejecuta `Nota::updateOrCreate` por matricula/asignacion/corte.

`NotaService` aplica los rangos:

- 90-100: `AA`;
- 76-89: `AS`;
- 60-75: `AF`;
- 0-59: `AI`.

Tambien calcula promedios semestrales y anuales con redondeo y determina aprobado desde 60. La entrada esta tipada como `?int`; la capa de request debe garantizar que no lleguen decimales si el negocio requiere enteros.

El indice de notas permite modo operativo para docentes y modo supervision para Director, Subdirector y Coordinador. La planilla carga solo matriculas activas del aula y notas del corte/asignacion seleccionados.

### Asistencia

La asistencia por aula busca al docente guia del usuario autenticado, el aula correspondiente al ciclo y sus matriculas activas. La asistencia por asignatura comprueba que el docente de la asignacion sea el usuario actual o que el usuario sea Director/Subdirector. Luego carga la asistencia del guia, incidencias del dia y bloques no recreativos de la modalidad.

La comprobacion de propiedad de la asignacion esta implementada directamente con `if` y `abort(403)`, no mediante una policy dedicada. Ademas, `destroy(AsistenciaAsignatura $incidencia)` elimina por binding sin una comprobacion visible de que la incidencia pertenezca al docente solicitante o a la asignacion que este administra. Es un punto de revision prioritario.

### Matriculas y bajas

`MatriculaController` ofrece alta, actualizacion, retiro y reactivacion. La migracion de soft deletes cubre `usuario`, `alumno`, `docente` y `matricula`, pero no todas las entidades relacionadas. La aplicacion combina el estado de matricula con `deleted_at`; ambos conceptos deben documentarse para no producir consultas que reintroduzcan registros retirados o eliminados.

## 7. Autenticacion, roles y autorizacion

`config/auth.php` configura el guard `web` con driver de sesion y proveedor Eloquent basado en `App\\Models\\Usuario`. El modelo `Usuario` implementa el contrato de autenticacion por herencia de `Illuminate\\Foundation\\Auth\\User`.

Spatie usa sus tablas predeterminadas `roles`, `permissions`, `model_has_roles`, `model_has_permissions` y `role_has_permissions`, separadas del catalogo propio `rol` y de `usuario.rol_id`. `PermisoSeeder` crea ambos conceptos: roles propios con nombres como `Docente Guia` y roles de Spatie con nombres como `Docente Guía`, ademas de `Gestor de Usuarios`, que no aparece en `RolSeeder`.

La autorizacion observada se reparte en tres mecanismos:

1. Policies invocadas con `$this->authorize(...)`.
2. Comprobaciones directas `hasRole(...)` y `hasPermissionTo(...)`.
3. Comprobaciones de identidad manuales, especialmente en asistencia por asignatura.

Riesgos concretos:

- `PermisoSeeder` define permisos `avance.gestionar`, `reparacion.gestionar` y `malla.ver`, mientras algunas policies consultan `avance_contenido.*`, `reparacion_examenes.*` y `mallas_curriculares.*`. Es probable que ciertas rutas fallen con denegacion aunque el rol parezca correctamente configurado.
- El nombre con tilde `Docente Guía` en Spatie no coincide con `Docente Guia` del catalogo propio. Las comparaciones por nombre deben normalizarse mediante constantes o un catalogo unico.
- Varias rutas solo usan `auth`; la proteccion real depende de que cada controlador invoque su policy. La politica debe probarse por endpoint, no inferirse del middleware del grupo.
- En `Usuario` la relacion `rol()` esta comentada. El campo `rol_id` existe y tiene FK a `rol`, pero el modelo no expone actualmente una relacion activa hacia `Rol`.

Recomendacion: elegir una fuente canonica para identidad de rol. Una estrategia conservadora es mantener `rol_id` solo si el dominio lo necesita y hacer que la sincronizacion con Spatie ocurra en un unico servicio transaccional, con nombres constantes y pruebas de matriz rol/permisos.

## 8. Problemas de integracion visibles

### Referencia a un modelo inexistente

La estructura observada contiene `App\\Models\\Usuario.php`, pero no `App\\Models\\User.php`. Sin embargo, el skeleton Breeze importa `App\\Models\\User` en `RegisteredUserController`, `NewPasswordController` y `ProfileUpdateRequest`. El registro de usuario y partes del perfil/password pueden fallar al ejecutarse con `Class "App\\Models\\User" not found`, aunque `config/auth.php` ya apunte correctamente a `Usuario`.

Ademas, el formulario Breeze usa campos `name` y crea `name`, mientras la tabla `usuario` exige `nombre_completo` y `rol_id` no nulo. El flujo de registro publico necesita una decision funcional: deshabilitar registro publico, adaptar el flujo a `Usuario` y asignar rol de forma segura, o crear una compatibilidad deliberada. No debe solucionarse creando silenciosamente un segundo modelo de usuario sin revisar autenticacion y permisos.

### Seeder y datos iniciales

`DatabaseSeeder` importa `App\\Models\\User` aunque no lo utiliza. Es ruido menor, pero evidencia que el skeleton no fue limpiado por completo. Los seeders de permisos usan roles Spatie, mientras `RolSeeder` usa el modelo `Rol`; se necesita una estrategia explicita de sincronizacion.

### Consistencia de permisos

Antes de crear nuevos controladores, comparar cada permiso usado en policies/controladores contra la lista de `PermisoSeeder`. Conviene agregar una prueba que recorra todos los strings de permisos y falle si alguno no existe.

## 9. Frontend y experiencia de usuario

`resources/views/layouts/app.blade.php` es el layout principal autenticado. Incluye Vite, SweetAlert2 y Chart.js desde CDN. Alpine controla el sidebar, persiste `sidebarOpen` en `localStorage`, colapsa el menu en pantallas menores de 1024 px y muestra un boton superior al hacer scroll.

La interfaz usa una direccion visual institucional con fondo claro, acentos amarillos/ambar, slate y tipografia Figtree cargada desde Bunny Fonts. La navegacion se incluye desde `layouts.navigation`. Las vistas de negocio estan separadas por areas bajo `resources/views/academico`.

Puntos de mantenimiento:

- SweetAlert2 y Chart.js no estan declarados en `package.json`; el layout depende de CDN y disponibilidad externa.
- Alpine aparece en dos secciones de `package.json`.
- Hay SVG inline repetidos para iconos; una futura normalizacion puede reducir duplicacion, pero no es requisito funcional.
- El uso de `localStorage` en `x-data` presupone navegador; no afecta el render servidor, pero debe mantenerse defensivo si se introduce SSR o pruebas de componentes.

## 10. Pruebas y calidad actual

La suite visible es principalmente la suite Breeze de autenticacion/perfil mas `ExampleTest` feature/unit. No se observan pruebas especificas para alumnos, matriculas, aulas, permisos, notas, horarios o asistencia.

Cobertura prioritaria recomendada:

1. autenticacion con `Usuario`, registro deshabilitado/adaptado y perfil;
2. matriz de autorizacion por rol y endpoint;
3. restricciones de propiedad en notas, horarios y asistencia;
4. calculos de `NotaService`, incluidos limites 0, 59, 60, 75, 76, 89, 90 y 100, mas entradas fuera de rango;
5. idempotencia de `updateOrCreate` para notas e incidencias;
6. aislamiento por ano escolar/modalidad/aula;
7. soft deletes y consultas de matriculas activas;
8. migraciones sobre SQLite de test y sobre el motor de produccion elegido.

Antes de interpretar resultados, habilitar `mbstring` en el PHP que ejecuta PHPUnit. Tambien instalar Node/npm y ejecutar `npm ci` o `npm install` segun exista lockfile, seguido de `npm run build`.

## 11. Operacion local y despliegue

Flujo declarado por Composer:

```powershell
composer install
php artisan key:generate
php artisan migrate --force
npm install --ignore-scripts
npm run build
```

Para desarrollo, `composer run dev` levanta simultaneamente:

- servidor PHP (`php artisan serve`);
- listener de cola (`php artisan queue:listen`);
- logs (`php artisan pail`);
- Vite (`npm run dev`).

El `.env.example` usa SQLite, sesiones en base de datos, cache en base de datos, cola en base de datos, correo por log y almacenamiento local. Para produccion hay que revisar como minimo `APP_ENV`, `APP_DEBUG=false`, `APP_KEY`, `APP_URL`, credenciales de base de datos, driver de sesiones/cache/cola, almacenamiento persistente, correo real y estrategia de logs.

No hay evidencia en el contexto inspeccionado de pipeline CI, contenedores, configuracion de servidor web, backups o monitoreo. Deben tratarse como trabajo pendiente, no como capacidades existentes.

## 12. Riesgos priorizados

### Criticos antes de seguir desarrollando

- Corregir la referencia Breeze a `App\\Models\\User` y decidir el comportamiento del registro publico.
- Habilitar y fijar las extensiones PHP requeridas, especialmente `mbstring`, en desarrollo, CI y produccion.
- Auditar la autorizacion de `destroy` de asistencia por asignatura y cualquier endpoint con comprobacion manual de propiedad.
- Alinear nombres de permisos entre seeders y policies.

### Altos

- Definir una unica fuente de verdad para roles (`rol` propio frente a tablas Spatie).
- Añadir pruebas de autorizacion por rol y pruebas de regresion para los modulos de fase 5/6.
- Confirmar el motor de base de datos objetivo y probar todas las migraciones desde cero; el `.env.example` usa SQLite, pero la presencia de configuracion Redis y comentarios MySQL sugiere que el despliegue puede cambiar de motor.

### Medios

- Eliminar la ruta duplicada de `gestor-horarios`.
- Consolidar Alpine en una sola dependencia y decidir si CDN de SweetAlert2/Chart.js es aceptable.
- Limpiar imports del skeleton y documentar la politica de SoftDeletes frente a estados de negocio.
- Añadir indices compuestos para consultas recurrentes cuando las mediciones confirmen necesidad, especialmente en notas, asistencia y asignaciones por ano/aula.

## 13. Guia para implementar cambios

1. Identificar primero el modelo y la migracion que son dueños del dato.
2. Mantener nombres de tabla y FK explicitos; no confiar en pluralizacion automatica para las tablas academicas.
3. Validar entrada en un Form Request cuando el flujo ya tenga uno; conservar reglas de rango, existencia y pertenencia contextual.
4. Autorizar antes de consultar o mutar datos sensibles. Preferir una policy reutilizable a una condicion inline.
5. Para operaciones por lote de notas/asistencia, usar transaccion e idempotencia.
6. Filtrar siempre por ano escolar, aula y estado activo cuando el caso de uso lo exija; no confiar solo en el ID recibido.
7. Añadir prueba feature del endpoint y prueba unitaria de la regla de dominio modificada.
8. Ejecutar `php artisan route:list`, `php artisan test` y `npm run build` con el entorno correctamente instalado.
9. Revisar cache de permisos despues de cambiar seeders o asignaciones (`PermissionRegistrar::forgetCachedPermissions()` o el flujo oficial equivalente).
10. No introducir API/Sanctum por asumir que el paquete instalado ya define un contrato API.

## 14. Archivos de referencia rapida

- Entrada de aplicacion: `bootstrap/app.php`.
- Rutas web: `routes/web.php` y `routes/auth.php`.
- Dependencias backend/frontend: `composer.json` y `package.json`.
- Autenticacion: `config/auth.php`, `app/Models/Usuario.php`.
- Permisos: `config/permission.php`, `database/seeders/PermisoSeeder.php`, `app/Policies/`.
- Calificaciones: `app/Http/Controllers/NotaController.php`, `app/Services/NotaService.php`, `app/Models/Nota.php`.
- Asistencia: `app/Http/Controllers/AsistenciaAulaController.php`, `app/Http/Controllers/AsistenciaAsignaturaController.php`, migraciones `create_asistencia_*`.
- Esquema: `database/migrations/`.
- Datos iniciales: `database/seeders/DatabaseSeeder.php` y seeders especializados.
- UI: `resources/views/layouts/app.blade.php`, `resources/js/app.js`, `resources/css/app.css`.
- Pruebas: `tests/Feature/` y `tests/Unit/`.

## 15. Conclusiones

El proyecto ya tiene una base de dominio amplia y una separacion razonable entre controladores, modelos, requests, policies, servicios y vistas. El flujo de calificaciones incorpora transacciones y reglas de negocio aisladas; el de asistencia por asignatura incorpora unicidad e idempotencia a nivel de base de datos.

El siguiente trabajo de mayor retorno no es agregar otro modulo, sino cerrar las fronteras existentes: unificar el modelo de usuario, asegurar que los permisos declarados sean los permisos consultados, convertir comprobaciones de propiedad en autorizacion testeable y hacer reproducible el entorno PHP/Node. Una vez resueltos esos puntos, la cobertura de tests de los flujos de fase 5 y fase 6 sera la mejor proteccion contra regresiones.
