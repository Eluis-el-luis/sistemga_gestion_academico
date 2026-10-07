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

El servicio `NotaService` encapsula la lógica de conversión y promedios:

- 90 a 100 => AA
- 76 a 89 => AS
- 60 a 75 => AF
- 0 a 59 => AI

Además, calcula promedios semestrales y anuales, y establece aprobado cuando la nota final es mayor o igual a 60.

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

## 15. Conclusión arquitectónica

Este proyecto es un sistema escolar monolítico con una lógica de negocio altamente especializada y una capa de acceso bien organizada en torno a modelos académicos, roles y permisos. Tiene un diseño serio y un dominio muy definido, pero requiere disciplina para evitar que la lógica de autorización, validación y filtrado por contexto se disperse en controladores.

Desde la perspectiva de un desarrollador full stack, el punto más importante es entender que esta aplicación no es una CRUD genérica: es un sistema de operación escolar con reglas de negocio y permisos profundamente conectados con el modelo institucional. La clave del mantenimiento futuro es preservar esa coherencia en cada módulo: usuarios, matrículas, aulas, horarios, notas, asistencia y reportes.

Para un profesional de backend y frontend, el proyecto combina tres capas bien diferenciadas:

- dominio académico y persistencia
- seguridad y autorización institucional
- UI con Blade/Tailwind/Alpine para gestión operativa

Eso convierte a este repositorio en una base sólida, pero con una dependencia directa de la calidad del diseño de reglas y de la disciplina de mantenimiento del código.
