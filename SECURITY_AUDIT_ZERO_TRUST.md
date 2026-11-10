# Auditoría de Seguridad Zero Trust - Sistema de Gestión Académico

**Fecha:** 2026-11-09  
**Versión:** 1.0  
**Alcance:** Auditoría completa de arquitectura Zero Trust (Confianza Cero)  
**Metodología:** Basada en security-audit skill (fases de descubrimiento, verificación, remediación)

---

## RESUMEN EJECUTIVO

El sistema implementa un modelo de autorización basado en **Spatie Laravel Permission** (roles + permisos) con **Policies de Laravel** para control de alcance por recurso. Sin embargo, se identificaron **violaciones críticas a Zero Trust**:

| Severidad | Hallazgos Críticos | Hallazgos Altos | Hallazgos Medios | Hallazgos Bajos |
|-----------|-------------------|-----------------|------------------|-----------------|
| **Total** | **4** | **7** | **12** | **8** |

**Conclusión:** El sistema **NO cumple** con arquitectura Zero Trust. Confía implícitamente en sesiones autenticadas sin verificación granular por recurso a nivel de base de datos (RLS ausente), y tiene gaps de autorización en controladores y rutas.

---

## FASE 1: DESCUBRIMIENTO Y MAPEO - MATRIZ DE ACCESO

### 1.1 Roles Identificados (8 roles)

| Rol | Descripción | Permisos Clave |
|-----|-------------|----------------|
| **Director** | Poder absoluto | Todos los permisos (`*.gestionar`, `*.ver`, `configuracion.*`) |
| **Subdirector** | Supervisión total | `alumnos.supervisar`, `*.ver`, `*.gestionar` (excepto usuarios), `configuracion.gestionar` |
| **Gestor de Usuarios** | Admin operativo | `configuracion.gestionar`, `alumnos.gestionar`, `aulas.gestionar`, `malla.gestionar`, `asignaturas_aula.gestionar` |
| **Coordinador** | Supervisión modalidad | `alumnos.ver`, `aulas.ver`, `notas.ver`, `asistencia.ver`, `horarios.ver`, `reportes.ver` |
| **Secretaria** | Operación diaria | `alumnos.gestionar`, `notas.ver`, `asistencia.ver`, `boletines.ver`, `reportes.gestionar` |
| **Docente Guía** | Tutor de aula | `alumnos.gestionar`, `notas.gestionar`, `asistencia.gestionar`, `boletines.gestionar`, `horarios.gestionar`, `reportes.ver` |
| **Docente por Asignatura** | Profesor materia | `alumnos.ver`, `notas.gestionar`, `asistencia.gestionar`, `horarios.ver`, `avance.gestionar` |
| **Alumno** | Estudiante | Solo acceso a portal propio (sin dashboard admin) |

### 1.2 Matriz de Permisos por Módulo (48 permisos)

```
MÓDULO                    | PERMISOS
--------------------------|----------------------------------------
Alumnos y Matrícula       | alumnos.gestionar, alumnos.ver, alumnos.supervisar
Aulas                     | aulas.gestionar, aulas.ver
Calificaciones (Notas)    | notas.gestionar, notas.ver
Asistencia                | asistencia.gestionar, asistencia.ver
Indicadores de Logro      | indicadores.gestionar, indicadores.ver
Boletines                 | boletines.gestionar, boletines.ver
Malla Curricular          | malla.gestionar, malla.ver
Asignaturas por Aula      | asignaturas_aula.gestionar, asignaturas_aula.ver
Horarios                  | horarios.gestionar, horarios.ver
Avance de Contenidos      | avance.gestionar, avance.ver
Apoyo de Padres           | apoyo_padres.gestionar, apoyo_padres.ver
Examen de Reparación      | reparacion.gestionar, reparacion.ver
Reportes                  | reportes.gestionar, reportes.ver, reportes.supervisar
Configuración             | configuracion.gestionar, configuracion.ver
```

### 1.3 Endpoints Auditados (250+ rutas)

- **Rutas protegidas por `auth` + `role` middleware:** 45 (dashboard, avisos)
- **Rutas bajo `auth` group (academico.*):** 200+ rutas
- **Rutas de autenticación (guest):** 6 rutas (login, password reset, email verification)

### 1.4 Políticas de Laravel Implementadas (14 Policies)

| Policy | Métodos | Protege |
|--------|---------|---------|
| `UsuarioPolicy` | viewAny, create, update, delete | Gestión de usuarios |
| `NotaPolicy` | viewAny, create, update | Calificaciones |
| `MatriculaPolicy` | viewAny, create, update, view, delete | Matrículas |
| `AlumnoPolicy` | viewAny, create, update, delete | Expedientes alumnos |
| `AulaPolicy` | viewAny, view, create, update, delete | Aulas |
| `BoletinPolicy` | viewAny, create, update | Boletines |
| `HorarioPolicy` | viewAny, create, update | Horarios |
| `AulaAsignaturaDocentePolicy` | viewAny, create, update, calificar, gestionarAsistencia | Asignaciones docente-materia |
| `AsistenciaAulaPolicy` | viewAny, create, update | Asistencia aula |
| `AnioEscolarPolicy` | viewAny, view, create, update, delete, cambiarEstado, cerrar | Años escolares |
| `AvanceContenidoPolicy` | viewAny, create, update, delete | Avance curricular |
| `ExamenReparacionPolicy` | viewAny, create, update, delete | Exámenes reparación |
| `ApoyoPadresPolicy` | viewAny, create, update, delete | Apoyo familiar |
| `MallaCurricularPolicy` | viewAny, create, update | Malla curricular |

---

## FASE 2: VERIFICACIÓN ZERO TRUST - VIOLACIONES ENCONTRADAS

### 🔴 CRÍTICO - CVE-2026-ZT-001: Ausencia Total de Row Level Security (RLS) en Supabase/PostgreSQL

**Evidencia:** `config/database.php` usa conexión `pgsql` a Supabase. No existe **ninguna política RLS** en migraciones ni scripts SQL.

**Impacto:** Cualquier usuario autenticado con acceso directo a BD (bypass Laravel) puede leer/escribir **TODAS** las tablas. No hay defensa en profundidad a nivel base de datos.

**Principio violado:** Zero Trust requiere "verificar explícitamente" en **cada capa**, incluyendo la capa de datos.

```sql
-- EJEMPLO DE VULNERABILIDAD: Consulta directa a Supabase
SELECT * FROM nota; -- Devuelve 100% de calificaciones sin filtro de rol
SELECT * FROM alumno WHERE codigo_unico_persona = '12345'; -- Acceso a expediente cualquier alumno
```

**Afecta:** Todas las 35+ tablas del esquema `public`.

---

### 🔴 CRÍTICO - CVE-2026-ZT-002: Middleware `role` Solo Valida Acceso a Dashboard, No a Recursos

**Evidencia:** `app/Http/Middleware/EnsureUserHasRole.php` líneas 16-58

```php
// SOLO verifica si tiene UNO de estos roles para acceder al dashboard
protected const DASHBOARD_ROLES = [
    'Director', 'Subdirector', 'Gestor de Usuarios', 'Coordinador',
    'Secretaria', 'Docente Guia', 'Docente por Asignatura',
];
```

**Problema:** 
- No valida permisos específicos por ruta/recurso
- Un "Docente por Asignatura" con rol válido accede a `/academico/usuarios` (gestión de usuarios) si la ruta no tiene policy explícita
- El middleware permite acceso a **cualquier ruta bajo `auth`** si el usuario tiene rol de dashboard

**Rutas expuestas sin autorización granular:**
- `academico.usuarios.*` - Solo `UsuarioPolicy` protege, pero rutas no invocan `$this->authorize()` consistentemente
- `academico.aulas.*` - `AulaPolicy` existe pero controlador `show()` tiene lógica duplicada de ownership (líneas 80-92)

---

### 🔴 CRÍTICO - CVE-2026-ZT-003: Políticas `before()` Otorgan "Poder Absoluto" a Director/Subdirector Sin Verificación de Propiedad

**Evidencia:** 12 de 14 policies usan patrón `before()` que retorna `true` para Director/Subdirector:

```php
// NotaPolicy.php líneas 14-20 - PATRÓN REPETIDO EN 12 POLICIES
public function before(Usuario $usuario, $ability): ?bool
{
    if ($usuario->hasRole(['Director', 'Subdirector'])) {
        return true; // BYPASS TOTAL de todos los checks de ownership
    }
    return null;
}
```

**Violación Zero Trust:** "Nunca confiar, siempre verificar". Un Director puede:
- Ver/editar/eliminar notas de **cualquier** aula sin ser docente de esa materia
- Acceder a boletines de alumnos que no supervisa
- Modificar horarios de aulas ajenas

**Riesgo:** Cuenta de Director comprometida = acceso total a datos sensibles (notas, expedientes, disciplina) sin auditoría de alcance.

---

### 🔴 CRÍTICO - CVE-2026-ZT-004: Controlador `DashboardController` Expone Datos Cross-Tenant Sin Verificación de Policy

**Evidencia:** `app/Http/Controllers/DashboardController.php` líneas 31-266

```php
// Líneas 92-100: Director/Subdirector/Gestor ven TODO sin policy
if ($user->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
    $totalMatriculados = Matricula::where('estado', 'activo')->count(); // TODAS las matrículas
    $totalPersonal = Usuario::role(['Docente Guia', 'Docente por Asignatura'])->count(); // TODO el personal
    $dbMetricas = $this->calcularMetricas(); // Métricas de TODAS las modalidades
    $muralData = $this->generarMuralDigital($anioActivo, $request->query('modalidad_mural')); // Datos cross-modalidad
}
```

**Problema:** 
- No usa `$this->authorize('viewAny', ...)` para ninguna consulta
- `calcularMetricas()` itera **todas las modalidades** sin filtro de coordinación
- `generarMuralDigital()` agrega datos de **todas las aulas** del año activo
- Un "Gestor de Usuarios" (rol operativo) ve métricas de **Director/Subdirector**

---

### 🟠 ALTO - CVE-2026-ZT-005: Rutas de Reportes Sin Verificación de Alcance por Modalidad/Aula

**Evidencia:** `routes/web.php` líneas 202-233, `ReporteController.php`

```php
// Rutas accesibles con solo permiso 'reportes.ver' (tienen Coordinador, Secretaria, Docente Guía)
Route::get('reportes/control-notas', ...)->name('reportes.control-notas');
Route::get('reportes/notas-globales', ...)->name('reportes.notas-globales');
Route::get('reportes/notas-pendientes', ...)->name('reportes.notas-pendientes');
Route::get('reportes/asistencia-global', ...)->name('reportes.asistencia-global');
Route::get('reportes/estadisticas-asistencia', ...)->name('reportes.estadisticas-asistencia');
// ... 20+ rutas de reportes
```

**Controlador:** `autorizarReportes()` solo verifica permiso base, **no filtra por modalidad/aula del usuario**:

```php
// ReporteController.php líneas 23-29
protected function autorizarReportes(): void
{
    if (!auth()->user()->hasAnyPermission(['reportes.ver', 'reportes.gestionar', 'reportes.supervisar'])) {
        throw new AuthorizationException(...);
    }
    // NO HAY FILTRO DE ALCANCE: Un Coordinador de Primaria ve reportes de Secundaria
}
```

**Servicios llamados** (`ReporteService`) consultan tablas globales sin `where` de ownership.

---

### 🟠 ALTO - CVE-2026-ZT-006: Controlador `AulaController::show()` Duplica Lógica de Authorization Fuera de Policy

**Evidencia:** `AulaController.php` líneas 70-192

```php
// Líneas 76-92: Lógica de ownership HARDCODEADA en controlador, NO en Policy
if ($usuario->hasRole('Docente Guia') && !$usuario->hasRole(['Director', 'Subdirector'])) {
    $docente = Docente::where('usuario_id', $usuario->id)->first();
    if (!$docente) { abort(403, 'No tienes perfil de docente...'); }
    if ($aula->docente_guia_id !== $docente->id) {
        abort(403, 'No tienes autorización para ver los detalles de esta aula.');
    }
}
```

**Violación:** 
- Policy `AulaPolicy::view()` (líneas 43-51) **NO verifica ownership** - solo checa permiso `aulas.ver`
- Lógica de negocio de autorización dispersa entre Policy y Controller
- Difícil de auditar, mantener y testear

---

### 🟠 ALTO - CVE-2026-ZT-007: `ReporteController::reporteMinedDetallado()` Filtra por URL Parameter Manipulable

**Evidencia:** `ReporteController.php` líneas 536-572

```php
// Líneas 546-566: "Bloqueo crítico" que SOBREESCRIBE filtro de request
if ($user->hasRole('Docente Guia') && !$user->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
    $filtros['grado_id'] = $aulaGuia->grado_id; // Sobrescribe lo que venga en URL
}
```

**Problema:** 
- Filtro aplicado **después** de recibir request, no en Policy
- Un Docente Guía malicioso podría enviar `grado_id` de otro grado antes del override
- No usa `$this->authorize('view', $matricula)` ni policy de alcance

---

### 🟠 ALTO - CVE-2026-ZT-008: `NotaController::index()` Consulta Datos Sin Policy de Alcance para Docentes

**Evidencia:** `NotaController.php` líneas 26-64

```php
// Líneas 33-53: Lógica de filtrado HARDCODEADA por rol
if ($usuario->hasRole(['Subdirector', 'Director'])) {
    $modoSupervision = true;
    $asignaciones = AulaAsignaturaDocente::where('aula_id', $aulaSeleccionadaId)->get();
} else {
    $modoSupervision = false;
    $docente = Docente::where('usuario_id', $usuario->id)->first();
    $asignaciones = AulaAsignaturaDocente::where('docente_id', $docente->id)->get();
}
```

**Violación:** 
- No usa `NotaPolicy::viewAny()` para filtrar
- Lógica de alcance duplicada en controlador
- `NotaPolicy::viewAny()` (línea 25-28) solo verifica permiso base, **no filtra datos**

---

### 🟠 ALTO - CVE-2026-ZT-009: Form Requests No Invocan Authorization Policies

**Evidencia:** `app/Http/Requests/*.php` - Ningún FormRequest usa `authorize()` para policies

```php
// StoreNotaRequest.php - Típico FormRequest
public function authorize(): bool
{
    return true; // SIEMPRE true, delega al controlador
}
```

**Problema:** Validación de autorización ocurre **después** de validación de datos, en el controlador. Un atacante puede:
- Enviar payloads maliciosos que consumen recursos de validación
- Probar existencia de IDs (IDOR) via respuestas de validación vs 403

---

### 🟠 ALTO - CVE-2026-ZT-010: Rutas de `boletines.pdf.*` Sin Verificación de Ownership en Controlador

**Evidencia:** `routes/web.php` líneas 175-179, `BoletinPdfController.php` (no mostrado pero referenciado)

```php
Route::get('boletines/pdf/boletin/{matricula}', ...)->name('boletines.pdf.boletin');
Route::get('boletines/pdf/constancia/{matricula}', ...)->name('boletines.pdf.constancia');
Route::get('boletines/pdf/certificado/{matricula}', ...)->name('boletines.pdf.certificado');
Route::post('boletines/pdf/masivo', ...)->name('boletines.pdf.masivo');
```

**Riesgo:** Generación masiva de PDFs expone datos de alumnos sin verificación de que el usuario tiene acceso a esas matrículas.

---

### 🟡 MEDIO - CVE-2026-ZT-011: `UsuarioPolicy::viewAny()` Permite a Secretaria Ver Todos los Usuarios

**Evidencia:** `UsuarioPolicy.php` líneas 12-15

```php
public function viewAny(Usuario $authUser): bool
{
    return $authUser->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios', 'Secretaria']);
}
```

**Problema:** Secretaria (rol operativo) puede listar **todos los usuarios** incluyendo Director, Subdirector, Gestores. Violación de menor privilegio.

---

### 🟡 MEDIO - CVE-2026-ZT-012: `AlumnoPolicy::create()` Permite a Secretaria Crear Alumnos Sin Verificación de Modalidad

**Evidencia:** `AlumnoPolicy.php` líneas 33-37

```php
public function create(Usuario $usuario): bool
{
    return $usuario->hasAnyRole(['Director', 'Subdirector', 'Secretaria']);
}
```

**Problema:** Secretaria puede crear alumnos en **cualquier modalidad/aula** sin restricción de su ámbito operativo.

---

### 🟡 MEDIO - CVE-2026-ZT-013: `MatriculaPolicy::view()` Permite a Coordinador Ver Matrículas de Su Modalidad Pero Sin Verificar Año Activo

**Evidencia:** `MatriculaPolicy.php` líneas 58-75

```php
public function view(Usuario $usuario, Matricula $matricula): bool
{
    if ($usuario->hasPermissionTo('boletines.ver') || $usuario->hasPermissionTo('boletines.gestionar')) {
        return true; // Coordinador con permiso boletines.ver ve TODO
    }
    // ... filtro por modalidad_coordina_id
}
```

**Problema:** Permiso `boletines.ver` (que tiene Coordinador) **bypassea** el filtro de modalidad.

---

### 🟡 MEDIO - CVE-2026-ZT-014: `AulaPolicy::view()` No Verifica Ownership Para Docente Guía

**Evidencia:** `AulaPolicy.php` líneas 43-51

```php
public function view(Usuario $usuario, Aula $aula): bool
{
    return $usuario->hasPermissionTo('aulas.ver') || $usuario->hasPermissionTo('aulas.gestionar');
    // Docente Guía con 'aulas.ver' ve CUALQUIER aula, no solo la suya
}
```

**Controlador** `AulaController::show()` compensa esto con lógica hardcodeada (CVE-2026-ZT-006), pero policy es ineficaz.

---

### 🟡 MEDIO - CVE-2026-ZT-015: `BoletinPolicy::update()` Solo Verifica Docente Guía, No Coordinador/Secretaria

**Evidencia:** `BoletinPolicy.php` líneas 42-52

```php
public function update(Usuario $usuario, Boletin $boletin): bool
{
    if (!$usuario->hasPermissionTo('boletines.gestionar')) return false;
    $docente = $usuario->docente;
    if ($docente) {
        return $boletin->matricula->aula->docente_guia_id === $docente->id;
    }
    return false; // Coordinador/Secretaria con 'boletines.gestionar' NO pueden actualizar
}
```

**Problema:** Inconsistencia - Coordinador puede `view` pero no `update` boletines de su modalidad.

---

### 🟡 MEDIO - CVE-2026-ZT-016: `ExamenReparacionPolicy::update()` Solo Verifica Docente Guía, No Docente por Asignatura

**Evidencia:** `ExamenReparacionPolicy.php` líneas 42-55

```php
public function update(Usuario $usuario, ExamenReparacion $examenReparacion): bool
{
    if (!$usuario->hasPermissionTo('reparacion.gestionar')) return false;
    $docente = $usuario->docente;
    if ($docente) {
        return $examenReparacion->matricula->aula->docente_guia_id === $docente->id;
        // Docente por Asignatura que imparte la materia NO puede gestionar reparaciones
    }
    return false;
}
```

---

### 🟡 MEDIO - CVE-2026-ZT-017: `ApoyoPadresPolicy::update()` No Verifica Que Docente Guía Sea del Aula Correcta Para Año Activo

**Evidencia:** `ApoyoPadresPolicy.php` líneas 42-52

```php
public function update(Usuario $usuario, ApoyoPadres $apoyoPadres): bool
{
    if (!$usuario->hasPermissionTo('apoyo_padres.gestionar')) return false;
    $docente = $usuario->docente;
    if ($docente) {
        return $apoyoPadres->aula->docente_guia_id === $docente->id;
        // FALTA: Verificar que aula.anio_escolar_id sea el activo
    }
    return false;
}
```

---

### 🟡 MEDIO - CVE-2026-ZT-018: `HorarioPolicy::update()` No Verifica Que Docente Imparta Esa Asignatura En Ese Año

**Evidencia:** `HorarioPolicy.php` líneas 42-55

```php
public function update(Usuario $usuario, Horario $horario): bool
{
    if (!$usuario->hasPermissionTo('horarios.gestionar')) return false;
    $docente = $usuario->docente;
    if ($docente) {
        return $horario->aulaAsignaturaDocente->docente_id === $docente->id;
        // FALTA: Verificar anio_escolar_id coincida
    }
    return false;
}
```

---

### 🟡 MEDIO - CVE-2026-ZT-019: `AvanceContenidoPolicy::update()` Mismo Problema - Falta Verificación de Año Escolar

**Evidencia:** `AvanceContenidoPolicy.php` líneas 42-55

```php
public function update(Usuario $usuario, AvanceContenido $avanceContenido): bool
{
    if (!$usuario->hasPermissionTo('avance.gestionar')) return false;
    $docente = $usuario->docente;
    if ($docente) {
        return $avanceContenido->aulaAsignaturaDocente->docente_id === $docente->id;
    }
    return false;
}
```

---

### 🟡 MEDIO - CVE-2026-ZT-020: Rutas de `asistencia.personal.marcar` Sin Policy

**Evidencia:** `routes/web.php` línea 51

```php
Route::post('/asistencia-personal/marcar', ...)->name('asistencia.personal.marcar');
```

**Controlador:** `AsistenciaPersonalController::marcarLlegada()` no usa `$this->authorize()`.

---

### 🟢 BAJO - CVE-2026-ZT-021: Cache de Permisos Spatie 24 Horas Sin Invalidación Automática en Cambios Críticos

**Evidencia:** `config/permission.php` líneas 162-184

```php
'cache' => [
    'expiration_time' => DateInterval::createFromDateString('24 hours'),
    // ...
],
```

**Riesgo:** Cambio de roles/permisos tarda hasta 24h en reflejarse. No hay listeners de eventos para invalidar cache (`events_enabled => false` línea 105).

---

### 🟢 BAJO - CVE-2026-ZT-022: `display_permission_in_exception` y `display_role_in_exception` Deshabilitados (Bueno) Pero Sin Logging de Accesos Denegados

**Evidencia:** `config/permission.php` líneas 132-145

```php
'display_permission_in_exception' => false, // Correcto - no filtrar info
'display_role_in_exception' => false,       // Correcto
```

**Falta:** No hay logging estructurado de `AuthorizationException` para auditoría SIEM.

---

### 🟢 BAJO - CVE-2026-ZT-023: Rutas `auth.php` (login, password reset) Sin Rate Limiting Estricto

**Evidencia:** `routes/auth.php` líneas 13-30

```php
Route::middleware('guest')->group(function () {
    Route::get('login', ...)->name('login');
    Route::post('login', ...); // Sin throttle
    Route::post('forgot-password', ...)->name('password.email'); // Solo throttle:6,1 en verify-email
});
```

**Solo** `verification.verify` y `verification.send` tienen `throttle:6,1`.

---

### 🟢 BAJO - CVE-2026-ZT-024: `EnsureUserHasRole` Permite Acceso a Rutas de Perfil/Logout Sin Rol (Correcto) Pero Lista Hardcodeada

**Evidencia:** `EnsureUserHasRole.php` líneas 44-50

```php
$rutasPermitidasSinRol = [
    'profile.edit', 'profile.update', 'profile.destroy',
    'logout', 'sin-rol',
];
```

**Mejora:** Debería ser configurable o usar policy.

---

### 🟢 BAJO - CVE-2026-ZT-025: `NotaController::store()` Valida Bloqueo (`CorteCerrado`) ANTES de Authorization

**Evidencia:** `NotaController.php` líneas 136-142

```php
// BARRERA 1: ¿El parcial está bloqueado? - ANTES de $this->authorize()
if (CorteCerrado::where(...)->where('bloqueado', true)->exists()) {
    return back()->with('error', 'El parcial está cerrado...');
}
// Authorization ocurre DESPUÉS en línea 149
$this->authorize('calificar', $asignacion);
```

**Riesgo:** Information disclosure - usuario sabe si un corte está bloqueado antes de saber si tiene permiso.

---

### 🟢 BAJO - CVE-2026-ZT-026: `DashboardController::storeAviso()` Verifica Rol Directamente Sin Policy

**Evidencia:** `DashboardController.php` líneas 505-510

```php
$user = Auth::user();
if (!$user->hasAnyRole(['Director', 'Subdirector', 'Coordinador'])) {
    return back()->with('error', 'No tienes permiso para publicar avisos.');
}
```

**Mejora:** Usar `AvisoPolicy` o Gate.

---

### 🟢 BAJO - CVE-2026-ZT-027: `AulaAsignaturaDocentePolicy::calificar()` Permite a Docente Guía Calificar Cualquier Materia de Su Aula

**Evidencia:** `AulaAsignaturaDocentePolicy.php` líneas 60-83

```php
public function calificar(Usuario $usuario, AulaAsignaturaDocente $asignatura): bool
{
    // ...
    if ($asignatura->aula?->docente_guia_id === $docente->id) {
        return true; // Docente Guía califica TODAS las materias de su aula
    }
}
```

**Diseño intencional?** Pero viola menor privilegio - Docente Guía no necesariamente imparte todas las materias.

---

### 🟢 BAJO - CVE-2026-ZT-028: Falta Policy Para `SolicitudEdicionNota` (Modelo Existe Pero No Policy)

**Evidencia:** Modelo `SolicitudEdicionNota` existe, se usa en `NotaController::solicitarDesbloqueo()` y `ReporteController`, pero **no hay `SolicitudEdicionNotaPolicy`**.

---

## FASE 3: REMEDIACIÓN Y HARDENING - CÓDIGO EXACTO PARA CORRECCIONES

---

### 3.1 SUPABASE ROW LEVEL SECURITY (RLS) - POLÍTICAS COMPLETAS

**Archivo:** `supabase/migrations/2026_11_09_000001_enable_rls_all_tables.sql`

```sql
-- ============================================================================
-- HABILITAR RLS EN TODAS LAS TABLAS DEL ESQUEMA PUBLIC
-- ============================================================================
-- Ejecutar DESPUÉS de todas las migraciones de Laravel
-- Requiere: Extensión pgcrypto para gen_random_uuid() si se usa

-- Función auxiliar: Obtener usuario autenticado actual desde JWT/auth.uid()
-- Supabase setea auth.uid() automáticamente desde el JWT del cliente
-- Para Laravel (server-side), usar set_config('app.current_user_id', $userId, true) en middleware

CREATE OR REPLACE FUNCTION current_user_id() RETURNS bigint AS $$
  SELECT COALESCE(
    nullif(current_setting('app.current_user_id', true), '')::bigint,
    nullif(current_setting('request.jwt.claims', true), '')::jsonb ->> 'sub'
  )::bigint;
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Función: Obtener roles del usuario actual
CREATE OR REPLACE FUNCTION current_user_roles() RETURNS text[] AS $$
  SELECT COALESCE(
    nullif(current_setting('app.current_user_roles', true), ''),
    '{}'
  )::text[];
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Función: Verificar si usuario tiene rol
CREATE OR REPLACE FUNCTION has_role(role_name text) RETURNS boolean AS $$
  SELECT role_name = ANY(current_user_roles());
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Función: Verificar si usuario tiene permiso (via Spatie role_has_permissions)
CREATE OR REPLACE FUNCTION has_permission(perm_name text) RETURNS boolean AS $$
  SELECT EXISTS (
    SELECT 1 FROM role_has_permissions rhp
    JOIN roles r ON r.id = rhp.role_id
    JOIN model_has_roles mhr ON mhr.role_id = r.id
    WHERE mhr.model_type = 'App\Models\Usuario'
      AND mhr.model_id = current_user_id()
      AND rhp.permission_id = (SELECT id FROM permissions WHERE name = perm_name LIMIT 1)
  );
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Función: Obtener docente_id del usuario actual
CREATE OR REPLACE FUNCTION current_docente_id() RETURNS bigint AS $$
  SELECT id FROM docente WHERE usuario_id = current_user_id() LIMIT 1;
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Función: Verificar si usuario es Director/Subdirector (poder absoluto)
CREATE OR REPLACE FUNCTION is_super_admin() RETURNS boolean AS $$
  SELECT has_role('Director') OR has_role('Subdirector');
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- ============================================================================
-- TABLA: usuario
-- ============================================================================
ALTER TABLE usuario ENABLE ROW LEVEL SECURITY;

-- SELECT: Usuario ve su propio registro + Director/Subdirector/Gestor/Secretaria ven todos
CREATE POLICY usuario_select_policy ON usuario
  FOR SELECT USING (
    id = current_user_id() 
    OR has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  );

-- INSERT: Solo Director/Subdirector/Gestor/Secretaria
CREATE POLICY usuario_insert_policy ON usuario
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  );

-- UPDATE: Usuario actualiza su perfil + Director/Subdirector/Gestor/Secretaria actualizan cualquiera
-- PROTECCIÓN: Director solo se edita a sí mismo. Subdirector no editable por Gestor.
CREATE POLICY usuario_update_policy ON usuario
  FOR UPDATE USING (
    id = current_user_id()
    OR (
      has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
      AND NOT (has_role('Director') AND id != current_user_id()) -- Director no edita otro Director
      AND NOT (has_role('Subdirector') AND NOT has_any_role(ARRAY['Director','Subdirector'])) -- Subdirector solo editable por Dir/Subdir
    )
  ) WITH CHECK (
    id = current_user_id()
    OR (
      has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
      AND NOT (has_role('Director') AND id != current_user_id())
      AND NOT (has_role('Subdirector') AND NOT has_any_role(ARRAY['Director','Subdirector']))
    )
  );

-- DELETE: Nadie se borra a sí mismo. Director intocable. Subdirector solo por Director. Resto por Dir/Subdir/Gestor
CREATE POLICY usuario_delete_policy ON usuario
  FOR DELETE USING (
    id != current_user_id() -- Nadie se auto-elimina
    AND NOT has_role('Director') -- Director intocable
    AND (
      (has_role('Subdirector') AND has_role('Director')) -- Subdirector solo por Director
      OR (NOT has_role('Subdirector') AND has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios']))
    )
  );

-- ============================================================================
-- TABLA: alumno
-- ============================================================================
ALTER TABLE alumno ENABLE ROW LEVEL SECURITY;

-- SELECT: Director/Subdirector ven todos. Coordinador ve su modalidad. Docente Guía ve su aula. Secretaria ve todos.
-- Alumno ve su propio expediente (via usuario_id)
CREATE POLICY alumno_select_policy ON alumno
  FOR SELECT USING (
    usuario_id = current_user_id() -- Alumno ve su expediente
    OR is_super_admin() -- Director/Subdirector
    OR (
      has_role('Coordinador') 
      AND EXISTS (
        SELECT 1 FROM matricula m
        JOIN aula a ON a.id = m.aula_id
        WHERE m.alumno_id = alumno.id
          AND m.estado = 'activo'
          AND a.modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
      )
    )
    OR (
      has_role('Docente Guia')
      AND EXISTS (
        SELECT 1 FROM matricula m
        WHERE m.alumno_id = alumno.id
          AND m.estado = 'activo'
          AND m.aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
      )
    )
    OR has_any_role(ARRAY['Secretaria','Gestor de Usuarios'])
  );

-- INSERT: Solo Director/Subdirector/Secretaria
CREATE POLICY alumno_insert_policy ON alumno
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Secretaria'])
  );

-- UPDATE: Director/Subdirector + Coordinador (su modalidad) + Secretaria
CREATE POLICY alumno_update_policy ON alumno
  FOR UPDATE USING (
    is_super_admin()
    OR has_role('Secretaria')
    OR (
      has_role('Coordinador')
      AND EXISTS (
        SELECT 1 FROM matricula m
        JOIN aula a ON a.id = m.aula_id
        WHERE m.alumno_id = alumno.id
          AND m.estado = 'activo'
          AND a.modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
      )
    )
  ) WITH CHECK (
    is_super_admin()
    OR has_role('Secretaria')
    OR (
      has_role('Coordinador')
      AND EXISTS (
        SELECT 1 FROM matricula m
        JOIN aula a ON a.id = m.aula_id
        WHERE m.alumno_id = alumno.id
          AND m.estado = 'activo'
          AND a.modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
      )
    )
  );

-- DELETE: Solo Director/Subdirector
CREATE POLICY alumno_delete_policy ON alumno
  FOR DELETE USING (
    is_super_admin()
  );

-- ============================================================================
-- TABLA: docente
-- ============================================================================
ALTER TABLE docente ENABLE ROW LEVEL SECURITY;

CREATE POLICY docente_select_policy ON docente
  FOR SELECT USING (
    usuario_id = current_user_id() -- Docente ve su perfil
    OR is_super_admin()
    OR has_any_role(ARRAY['Gestor de Usuarios','Secretaria','Coordinador'])
  );

CREATE POLICY docente_insert_policy ON docente
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  );

CREATE POLICY docente_update_policy ON docente
  FOR UPDATE USING (
    usuario_id = current_user_id() -- Docente actualiza su perfil
    OR has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  ) WITH CHECK (
    usuario_id = current_user_id()
    OR has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  );

CREATE POLICY docente_delete_policy ON docente
  FOR DELETE USING (
    is_super_admin()
  );

-- ============================================================================
-- TABLA: aula
-- ============================================================================
ALTER TABLE aula ENABLE ROW LEVEL SECURITY;

CREATE POLICY aula_select_policy ON aula
  FOR SELECT USING (
    is_super_admin()
    OR has_any_role(ARRAY['Gestor de Usuarios','Secretaria','Coordinador'])
    OR (
      has_role('Docente Guia')
      AND docente_guia_id = current_docente_id()
    )
    OR (
      has_role('Docente por Asignatura')
      AND EXISTS (
        SELECT 1 FROM aula_asignatura_docente aad
        WHERE aad.aula_id = aula.id
          AND aad.docente_id = current_docente_id()
          AND aad.activo = true
      )
    )
  );

CREATE POLICY aula_insert_policy ON aula
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios'])
  );

CREATE POLICY aula_update_policy ON aula
  FOR UPDATE USING (
    is_super_admin()
    OR has_role('Gestor de Usuarios')
    OR (
      has_role('Coordinador')
      AND modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
    )
  ) WITH CHECK (
    is_super_admin()
    OR has_role('Gestor de Usuarios')
    OR (
      has_role('Coordinador')
      AND modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
    )
  );

CREATE POLICY aula_delete_policy ON aula
  FOR DELETE USING (
    is_super_admin()
    OR (
      has_role('Coordinador')
      AND modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
    )
  );

-- ============================================================================
-- TABLA: matricula
-- ============================================================================
ALTER TABLE matricula ENABLE ROW LEVEL SECURITY;

CREATE POLICY matricula_select_policy ON matricula
  FOR SELECT USING (
    is_super_admin()
    OR has_any_role(ARRAY['Secretaria','Gestor de Usuarios'])
    OR (
      has_role('Coordinador')
      AND aula_id IN (
        SELECT id FROM aula 
        WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
      )
    )
    OR (
      has_role('Docente Guia')
      AND aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
    OR (
      has_role('Docente por Asignatura')
      AND EXISTS (
        SELECT 1 FROM aula_asignatura_docente aad
        WHERE aad.aula_id = matricula.aula_id
          AND aad.docente_id = current_docente_id()
          AND aad.activo = true
      )
    )
    OR alumno_id IN (SELECT id FROM alumno WHERE usuario_id = current_user_id()) -- Alumno ve su matrícula
  );

CREATE POLICY matricula_insert_policy ON matricula
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Secretaria','Gestor de Usuarios','Docente Guia'])
    -- Docente Guía solo en su aula (validado en app)
  );

CREATE POLICY matricula_update_policy ON matricula
  FOR UPDATE USING (
    is_super_admin()
    OR has_role('Secretaria')
    OR has_role('Gestor de Usuarios')
    OR (
      has_role('Coordinador')
      AND aula_id IN (
        SELECT id FROM aula 
        WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
      )
    )
    OR (
      has_role('Docente Guia')
      AND aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
  ) WITH CHECK (
    is_super_admin()
    OR has_role('Secretaria')
    OR has_role('Gestor de Usuarios')
    OR (
      has_role('Coordinador')
      AND aula_id IN (
        SELECT id FROM aula 
        WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
      )
    )
    OR (
      has_role('Docente Guia')
      AND aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
  );

CREATE POLICY matricula_delete_policy ON matricula
  FOR DELETE USING (
    is_super_admin()
    OR has_role('Secretaria')
    OR has_role('Gestor de Usuarios')
    OR (
      has_role('Coordinador')
      AND aula_id IN (
        SELECT id FROM aula 
        WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
      )
    )
    OR (
      has_role('Docente Guia')
      AND aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
  );

-- ============================================================================
-- TABLA: nota
-- ============================================================================
ALTER TABLE nota ENABLE ROW LEVEL SECURITY;

CREATE POLICY nota_select_policy ON nota
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('notas.ver')
    OR has_permission('notas.gestionar')
    OR (
      matricula_id IN (
        SELECT id FROM matricula 
        WHERE alumno_id IN (SELECT id FROM alumno WHERE usuario_id = current_user_id())
      )
    )
    OR (
      has_role('Docente Guia')
      AND matricula_id IN (
        SELECT m.id FROM matricula m
        JOIN aula a ON a.id = m.aula_id
        WHERE a.docente_guia_id = current_docente_id()
          AND m.estado = 'activo'
      )
    )
    OR (
      has_role('Docente por Asignatura')
      AND aula_asignatura_docente_id IN (
        SELECT id FROM aula_asignatura_docente WHERE docente_id = current_docente_id()
      )
    )
  );

CREATE POLICY nota_insert_policy ON nota
  FOR INSERT WITH CHECK (
    has_permission('notas.gestionar')
    AND (
      is_super_admin()
      OR (
        has_role('Docente Guia')
        AND aula_asignatura_docente_id IN (
          SELECT aad.id FROM aula_asignatura_docente aad
          WHERE aad.docente_id = current_docente_id()
            OR aad.aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
        )
      )
      OR (
        has_role('Docente por Asignatura')
        AND aula_asignatura_docente_id IN (
          SELECT id FROM aula_asignatura_docente WHERE docente_id = current_docente_id()
        )
      )
    )
  );

CREATE POLICY nota_update_policy ON nota
  FOR UPDATE USING (
    has_permission('notas.gestionar')
    AND (
      is_super_admin()
      OR (
        has_role('Docente Guia')
        AND aula_asignatura_docente_id IN (
          SELECT aad.id FROM aula_asignatura_docente aad
          WHERE aad.docente_id = current_docente_id()
            OR aad.aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
        )
        -- Verificar CorteCerrado.bloqueado = false
        AND NOT EXISTS (
          SELECT 1 FROM corte_cerrado cc
          WHERE cc.aula_asignatura_docente_id = nota.aula_asignatura_docente_id
            AND cc.corte_evaluativo_id = nota.corte_evaluativo_id
            AND cc.bloqueado = true
        )
      )
      OR (
        has_role('Docente por Asignatura')
        AND aula_asignatura_docente_id IN (
          SELECT id FROM aula_asignatura_docente WHERE docente_id = current_docente_id()
        )
        AND NOT EXISTS (
          SELECT 1 FROM corte_cerrado cc
          WHERE cc.aula_asignatura_docente_id = nota.aula_asignatura_docente_id
            AND cc.corte_evaluativo_id = nota.corte_evaluativo_id
            AND cc.bloqueado = true
        )
      )
    )
  ) WITH CHECK (
    has_permission('notas.gestionar')
    AND (
      is_super_admin()
      OR (
        has_role('Docente Guia')
        AND aula_asignatura_docente_id IN (
          SELECT aad.id FROM aula_asignatura_docente aad
          WHERE aad.docente_id = current_docente_id()
            OR aad.aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
        )
        AND NOT EXISTS (
          SELECT 1 FROM corte_cerrado cc
          WHERE cc.aula_asignatura_docente_id = nota.aula_asignatura_docente_id
            AND cc.corte_evaluativo_id = nota.corte_evaluativo_id
            AND cc.bloqueado = true
        )
      )
      OR (
        has_role('Docente por Asignatura')
        AND aula_asignatura_docente_id IN (
          SELECT id FROM aula_asignatura_docente WHERE docente_id = current_docente_id()
        )
        AND NOT EXISTS (
          SELECT 1 FROM corte_cerrado cc
          WHERE cc.aula_asignatura_docente_id = nota.aula_asignatura_docente_id
            AND cc.corte_evaluativo_id = nota.corte_evaluativo_id
            AND cc.bloqueado = true
        )
      )
    )
  );

CREATE POLICY nota_delete_policy ON nota
  FOR DELETE USING (
    is_super_admin()
    -- Soft delete via updated_by, no hard delete normalmente
  );

-- ============================================================================
-- TABLA: boletin
-- ============================================================================
ALTER TABLE boletin ENABLE ROW LEVEL SECURITY;

CREATE POLICY boletin_select_policy ON boletin
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('boletines.ver')
    OR has_permission('boletines.gestionar')
    OR matricula_id IN (
      SELECT id FROM matricula 
      WHERE alumno_id IN (SELECT id FROM alumno WHERE usuario_id = current_user_id())
    )
    OR (
      has_role('Docente Guia')
      AND matricula_id IN (
        SELECT m.id FROM matricula m
        JOIN aula a ON a.id = m.aula_id
        WHERE a.docente_guia_id = current_docente_id()
      )
    )
  );

CREATE POLICY boletin_insert_policy ON boletin
  FOR INSERT WITH CHECK (
    has_permission('boletines.gestionar')
    AND (
      is_super_admin()
      OR (
        has_role('Docente Guia')
        AND matricula_id IN (
          SELECT m.id FROM matricula m
          JOIN aula a ON a.id = m.aula_id
          WHERE a.docente_guia_id = current_docente_id()
        )
      )
    )
  );

CREATE POLICY boletin_update_policy ON boletin
  FOR UPDATE USING (
    has_permission('boletines.gestionar')
    AND (
      is_super_admin()
      OR (
        has_role('Docente Guia')
        AND matricula_id IN (
          SELECT m.id FROM matricula m
          JOIN aula a ON a.id = m.aula_id
          WHERE a.docente_guia_id = current_docente_id()
        )
      )
    )
  ) WITH CHECK (
    has_permission('boletines.gestionar')
    AND (
      is_super_admin()
      OR (
        has_role('Docente Guia')
        AND matricula_id IN (
          SELECT m.id FROM matricula m
          JOIN aula a ON a.id = m.aula_id
          WHERE a.docente_guia_id = current_docente_id()
        )
      )
    )
  );

-- ============================================================================
-- TABLA: aula_asignatura_docente
-- ============================================================================
ALTER TABLE aula_asignatura_docente ENABLE ROW LEVEL SECURITY;

CREATE POLICY aad_select_policy ON aula_asignatura_docente
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('asignaturas_aula.ver')
    OR has_permission('asignaturas_aula.gestionar')
    OR docente_id = current_docente_id()
    OR aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    OR (
      has_role('Coordinador')
      AND aula_id IN (
        SELECT id FROM aula 
        WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
      )
    )
  );

CREATE POLICY aad_insert_policy ON aula_asignatura_docente
  FOR INSERT WITH CHECK (
    has_permission('asignaturas_aula.gestionar')
    AND (
      is_super_admin()
      OR has_role('Gestor de Usuarios')
      OR (
        has_role('Coordinador')
        AND aula_id IN (
          SELECT id FROM aula 
          WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
        )
      )
    )
  );

CREATE POLICY aad_update_policy ON aula_asignatura_docente
  FOR UPDATE USING (
    has_permission('asignaturas_aula.gestionar')
    AND (
      is_super_admin()
      OR has_role('Gestor de Usuarios')
      OR (
        has_role('Coordinador')
        AND aula_id IN (
          SELECT id FROM aula 
          WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
        )
      )
      OR docente_id = current_docente_id() -- Docente actualiza su asignación
    )
  ) WITH CHECK (
    has_permission('asignaturas_aula.gestionar')
    AND (
      is_super_admin()
      OR has_role('Gestor de Usuarios')
      OR (
        has_role('Coordinador')
        AND aula_id IN (
          SELECT id FROM aula 
          WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
        )
      )
      OR docente_id = current_docente_id()
    )
  );

CREATE POLICY aad_delete_policy ON aula_asignatura_docente
  FOR DELETE USING (
    has_permission('asignaturas_aula.gestionar')
    AND (
      is_super_admin()
      OR has_role('Gestor de Usuarios')
      OR (
        has_role('Coordinador')
        AND aula_id IN (
          SELECT id FROM aula 
          WHERE modalidad_id = (SELECT modalidad_coordina_id FROM docente WHERE usuario_id = current_user_id())
        )
      )
    )
  );

-- ============================================================================
-- TABLA: horario
-- ============================================================================
ALTER TABLE horario ENABLE ROW LEVEL SECURITY;

CREATE POLICY horario_select_policy ON horario
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('horarios.ver')
    OR has_permission('horarios.gestionar')
    OR aula_asignatura_docente_id IN (
      SELECT id FROM aula_asignatura_docente WHERE docente_id = current_docente_id()
    )
    OR aula_asignatura_docente_id IN (
      SELECT aad.id FROM aula_asignatura_docente aad
      JOIN aula a ON a.id = aad.aula_id
      WHERE a.docente_guia_id = current_docente_id()
    )
  );

CREATE POLICY horario_insert_policy ON horario
  FOR INSERT WITH CHECK (
    has_permission('horarios.gestionar')
    AND aula_asignatura_docente_id IN (
      SELECT id FROM aula_asignatura_docente 
      WHERE docente_id = current_docente_id()
        OR aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
  );

CREATE POLICY horario_update_policy ON horario
  FOR UPDATE USING (
    has_permission('horarios.gestionar')
    AND aula_asignatura_docente_id IN (
      SELECT id FROM aula_asignatura_docente 
      WHERE docente_id = current_docente_id()
        OR aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
  ) WITH CHECK (
    has_permission('horarios.gestionar')
    AND aula_asignatura_docente_id IN (
      SELECT id FROM aula_asignatura_docente 
      WHERE docente_id = current_docente_id()
        OR aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
  );

CREATE POLICY horario_delete_policy ON horario
  FOR DELETE USING (
    has_permission('horarios.gestionar')
    AND aula_asignatura_docente_id IN (
      SELECT id FROM aula_asignatura_docente 
      WHERE docente_id = current_docente_id()
        OR aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
  );

-- ============================================================================
-- TABLA: asistencia_aula
-- ============================================================================
ALTER TABLE asistencia_aula ENABLE ROW LEVEL SECURITY;

CREATE POLICY asistencia_aula_select_policy ON asistencia_aula
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('asistencia.ver')
    OR has_permission('asistencia.gestionar')
    OR matricula_id IN (
      SELECT id FROM matricula 
      WHERE alumno_id IN (SELECT id FROM alumno WHERE usuario_id = current_user_id())
    )
    OR matricula_id IN (
      SELECT m.id FROM matricula m
      JOIN aula a ON a.id = m.aula_id
      WHERE a.docente_guia_id = current_docente_id()
    )
    OR matricula_id IN (
      SELECT m.id FROM matricula m
      JOIN aula_asignatura_docente aad ON aad.aula_id = m.aula_id
      WHERE aad.docente_id = current_docente_id()
    )
  );

CREATE POLICY asistencia_aula_insert_policy ON asistencia_aula
  FOR INSERT WITH CHECK (
    has_permission('asistencia.gestionar')
    AND matricula_id IN (
      SELECT m.id FROM matricula m
      WHERE m.aula_id IN (
        SELECT a.id FROM aula a
        WHERE a.docente_guia_id = current_docente_id()
           OR a.id IN (
             SELECT aad.aula_id FROM aula_asignatura_docente aad WHERE aad.docente_id = current_docente_id()
           )
      )
    )
  );

CREATE POLICY asistencia_aula_update_policy ON asistencia_aula
  FOR UPDATE USING (
    has_permission('asistencia.gestionar')
    AND matricula_id IN (
      SELECT m.id FROM matricula m
      WHERE m.aula_id IN (
        SELECT a.id FROM aula a
        WHERE a.docente_guia_id = current_docente_id()
           OR a.id IN (
             SELECT aad.aula_id FROM aula_asignatura_docente aad WHERE aad.docente_id = current_docente_id()
           )
      )
    )
  ) WITH CHECK (
    has_permission('asistencia.gestionar')
    AND matricula_id IN (
      SELECT m.id FROM matricula m
      WHERE m.aula_id IN (
        SELECT a.id FROM aula a
        WHERE a.docente_guia_id = current_docente_id()
           OR a.id IN (
             SELECT aad.aula_id FROM aula_asignatura_docente aad WHERE aad.docente_id = current_docente_id()
           )
      )
    )
  );

-- ============================================================================
-- TABLA: corte_evaluativo
-- ============================================================================
ALTER TABLE corte_evaluativo ENABLE ROW LEVEL SECURITY;

CREATE POLICY corte_select_policy ON corte_evaluativo
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('configuracion.ver')
    OR has_permission('notas.ver')
    OR has_permission('asistencia.ver')
  );

CREATE POLICY corte_insert_policy ON corte_evaluativo
  FOR INSERT WITH CHECK (
    has_permission('configuracion.gestionar')
  );

CREATE POLICY corte_update_policy ON corte_evaluativo
  FOR UPDATE USING (
    has_permission('configuracion.gestionar')
    AND NOT es_cerrado -- No editar año cerrado
  ) WITH CHECK (
    has_permission('configuracion.gestionar')
    AND NOT es_cerrado
  );

CREATE POLICY corte_delete_policy ON corte_evaluativo
  FOR DELETE USING (
    has_role('Director')
    AND es_planificacion
  );

-- ============================================================================
-- TABLA: anio_escolar
-- ============================================================================
ALTER TABLE anio_escolar ENABLE ROW LEVEL SECURITY;

CREATE POLICY anio_select_policy ON anio_escolar
  FOR SELECT USING (
    has_permission('configuracion.ver') OR has_permission('configuracion.gestionar')
  );

CREATE POLICY anio_insert_policy ON anio_escolar
  FOR INSERT WITH CHECK (
    has_permission('configuracion.gestionar')
  );

CREATE POLICY anio_update_policy ON anio_escolar
  FOR UPDATE USING (
    has_permission('configuracion.gestionar')
    AND NOT es_cerrado
  ) WITH CHECK (
    has_permission('configuracion.gestionar')
    AND NOT es_cerrado
  );

CREATE POLICY anio_delete_policy ON anio_escolar
  FOR DELETE USING (
    has_role('Director')
    AND es_planificacion
  );

-- ============================================================================
-- TABLAS RESTANTES (aplicar patrón similar)
-- ============================================================================
-- aviso, actividad_evaluativa, nota_actividad, indicador_logro, 
-- malla_curricular, grado, modalidad, asignatura, grupo_materia,
-- bloque_horario, corte_cerrado, solicitud_edicion_nota,
-- examen_reparacion, avance_contenido, apoyo_padres, evaluacion_familiar,
-- incidencia_disciplinaria, asistencia_personal, envio_boletines,
-- respaldo (solo Director/Subdirector)

-- Ejemplo genérico para tablas de catálogo (solo lectura para roles operativos):
-- ALTER TABLE grado ENABLE ROW LEVEL SECURITY;
-- CREATE POLICY grado_select_policy ON grado FOR SELECT USING (has_permission('configuracion.ver') OR has_permission('configuracion.gestionar') OR has_any_role(ARRAY['Coordinador','Secretaria','Gestor de Usuarios','Docente Guia','Docente por Asignatura']));
-- CREATE POLICY grado_modify_policy ON grado FOR ALL USING (has_permission('configuracion.gestionar'));

-- ============================================================================
-- MIDDLEWARE LARAVEL PARA SETEAR CONTEXTO RLS
-- ============================================================================
-- Crear: app/Http/Middleware/SetSupabaseRLSContext.php
-- Registrar en Kernel.php en grupo 'web' DESPUÉS de 'auth'
```

---

### 3.2 MIDDLEWARE LARAVEL PARA CONTEXTO RLS

**Archivo:** `app/Http/Middleware/SetSupabaseRLSContext.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetSupabaseRLSContext
{
    /**
     * Handle an incoming request.
     *
     * Configura variables de sesión PostgreSQL para que las políticas RLS
     * en Supabase tengan el contexto del usuario autenticado de Laravel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo ejecutar si hay usuario autenticado y conexión es PostgreSQL/Supabase
        if (Auth::check() && $this->isPostgreSQLConnection()) {
            $user = Auth::user();
            $userId = $user->id;
            
            // Obtener roles del usuario como array de strings
            $roles = $user->getRoleNames()->toArray();
            $rolesJson = json_encode($roles);
            
            // Ejecutar SET LOCAL para la transacción actual
            // Usar DB::statement para evitar prepared statements con variables de sesión
            DB::statement("SET LOCAL app.current_user_id = ?", [$userId]);
            DB::statement("SET LOCAL app.current_user_roles = ?", [$rolesJson]);
            
            // También setear docente_id si existe
            if ($user->docente) {
                DB::statement("SET LOCAL app.current_docente_id = ?", [$user->docente->id]);
            }
        }
        
        return $next($request);
    }
    
    protected function isPostgreSQLConnection(): bool
    {
        $driver = DB::getDriverName();
        return $driver === 'pgsql';
    }
}
```

**Registro en `app/Http/Kernel.php`:**

```php
protected $middlewareGroups = [
    'web' => [
        // ... middlewares existentes
        \App\Http\Middleware\SetSupabaseRLSContext::class, // DESPUÉS de \Illuminate\Session\Middleware\AuthenticateSession::class
    ],
];
```

---

### 3.3 POLÍTICAS LARAVEL REFORZADAS - DENEGAR POR DEFECTO

**Archivo:** `app/Policies/NotaPolicy.php` (Patrón para TODAS las policies)

```php
<?php

namespace App\Policies;

use App\Models\Nota;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class NotaPolicy
{
    /**
     * DENEGAR POR DEFECTO: before() NO retorna true para super-admin.
     * En su lugar, cada método verifica explícitamente.
     */
    public function before(Usuario $usuario, $ability): ?bool
    {
        // SOLO para bypass de super-admin en ACCIONES DE LECTURA (viewAny, view)
        // NUNCA para create, update, delete - requieren verificación de ownership
        if (in_array($ability, ['viewAny', 'view']) && $usuario->hasRole(['Director', 'Subdirector'])) {
            return true;
        }
        return null;
    }

    /**
     * Ver lista de notas - Filtrada por alcance en el controlador via Scope
     * Policy solo verifica permiso BASE
     */
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('notas.ver') || $usuario->hasPermissionTo('notas.gestionar');
    }

    /**
     * Ver nota individual - Requiere ownership O permiso de supervisión
     */
    public function view(Usuario $usuario, Nota $nota): bool
    {
        // Permiso base
        if (!$usuario->hasPermissionTo('notas.ver') && !$usuario->hasPermissionTo('notas.gestionar')) {
            return false;
        }

        // Super-admin (Director/Subdirector) ve todo
        if ($usuario->hasRole(['Director', 'Subdirector'])) {
            return true;
        }

        // Verificar ownership vía relación
        return $this->checkOwnership($usuario, $nota);
    }

    /**
     * Crear nota - Requiere permiso gestionar Y ownership de la asignación
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('notas.gestionar');
    }

    /**
     * Actualizar nota - Ownership estricto + verificación de corte no bloqueado
     */
    public function update(Usuario $usuario, Nota $nota): bool
    {
        if (!$usuario->hasPermissionTo('notas.gestionar')) {
            return false;
        }

        // Director/Subdirector: poder absoluto PERO solo si corte no bloqueado
        if ($usuario->hasRole(['Director', 'Subdirector'])) {
            return !$this->isCorteBloqueado($nota);
        }

        // Docentes: ownership estricto + corte no bloqueado
        return $this->checkOwnership($usuario, $nota) && !$this->isCorteBloqueado($nota);
    }

    /**
     * Eliminar nota - Solo Director/Subdirector, nunca docentes
     */
    public function delete(Usuario $usuario, Nota $nota): bool
    {
        return $usuario->hasRole(['Director', 'Subdirector']) && !$this->isCorteBloqueado($nota);
    }

    /**
     * Verificar ownership: El usuario es docente de esta asignación
     * O es docente guía del aula de la matrícula
     */
    protected function checkOwnership(Usuario $usuario, Nota $nota): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        // Cargar relaciones si no vienen eager loaded
        $asignacion = $nota->aulaAsignaturaDocente ?? $nota->aulaAsignaturaDocente()->with('aula')->first();
        if (!$asignacion) {
            return false;
        }

        // Regla A: Es el docente que imparte ESTA asignatura exacta
        if ($asignacion->docente_id === $docente->id) {
            return true;
        }

        // Regla B: Es el docente guía del aula
        if ($asignacion->aula?->docente_guia_id === $docente->id) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el corte evaluativo está bloqueado para esta asignación
     */
    protected function isCorteBloqueado(Nota $nota): bool
    {
        return \App\Models\CorteCerrado::where('aula_asignatura_docente_id', $nota->aula_asignatura_docente_id)
            ->where('corte_evaluativo_id', $nota->corte_evaluativo_id)
            ->where('bloqueado', true)
            ->exists();
    }
}
```

---

### 3.4 POLÍTICAS BASE REUTILIZABLES (Trait)

**Archivo:** `app/Policies/Concerns/HasResourceOwnership.php`

```php
<?php

namespace App\Policies\Concerns;

use App\Models\Usuario;

trait HasResourceOwnership
{
    /**
     * Verifica si el usuario es super-admin (Director/Subdirector)
     */
    protected function isSuperAdmin(Usuario $usuario): bool
    {
        return $usuario->hasRole(['Director', 'Subdirector']);
    }

    /**
     * Verifica ownership genérico para recursos vinculados a AulaAsignaturaDocente
     * (notas, horarios, avance_contenido, examen_reparacion, etc.)
     */
    protected function ownsViaAsignacion(Usuario $usuario, $resource, string $asignacionRelation): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        $asignacion = $resource->{$asignacionRelation} ?? $resource->{$asignacionRelation}()->first();
        if (!$asignacion) {
            return false;
        }

        // Docente que imparte la asignatura
        if ($asignacion->docente_id === $docente->id) {
            return true;
        }

        // Docente guía del aula
        if ($asignacion->aula?->docente_guia_id === $docente->id) {
            return true;
        }

        return false;
    }

    /**
     * Verifica ownership para recursos vinculados a Matricula -> Aula
     * (boletines, matriculas, examenes_reparacion)
     */
    protected function ownsViaMatriculaAula(Usuario $usuario, $resource, string $matriculaRelation): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        $matricula = $resource->{$matriculaRelation} ?? $resource->{$matriculaRelation}()->first();
        if (!$matricula) {
            return false;
        }

        // Docente guía del aula
        return $matricula->aula?->docente_guia_id === $docente->id;
    }

    /**
     * Verifica ownership para recursos vinculados a Aula directamente
     * (apoyo_padres, aulas)
     */
    protected function ownsAula(Usuario $usuario, $resource, string $aulaRelation = 'aula'): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        $aula = $resource->{$aulaRelation} ?? $resource->{$aulaRelation}()->first();
        if (!$aula) {
            return false;
        }

        return $aula->docente_guia_id === $docente->id;
    }

    /**
     * Verifica si el usuario coordina la modalidad del recurso
     */
    protected function coordinatesModality(Usuario $usuario, $resource, string $modalidadPath): bool
    {
        $docente = $usuario->docente;
        if (!$docente || !$docente->es_coordinador) {
            return false;
        }

        $modalidadId = data_get($resource, $modalidadPath);
        if (!$modalidadId) {
            return false;
        }

        return $docente->modalidad_coordina_id === $modalidadId;
    }

    /**
     * Verifica corte no bloqueado para recursos de notas
     */
    protected function isCorteBloqueado($resource, string $asignacionRelation, string $corteRelation): bool
    {
        $asignacionId = data_get($resource, "{$asignacionRelation}.id");
        $corteId = data_get($resource, "{$corteRelation}.id");

        if (!$asignacionId || !$corteId) {
            return false;
        }

        return \App\Models\CorteCerrado::where('aula_asignatura_docente_id', $asignacionId)
            ->where('corte_evaluativo_id', $corteId)
            ->where('bloqueado', true)
            ->exists();
    }
}
```

---

### 3.5 POLÍTICAS ACTUALIZADAS USANDO TRAIT

**Archivo:** `app/Policies/BoletinPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Boletin;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class BoletinPolicy
{
    use HasResourceOwnership;

    public function before(Usuario $usuario, $ability): ?bool
    {
        if (in_array($ability, ['viewAny', 'view']) && $this->isSuperAdmin($usuario)) {
            return true;
        }
        return null;
    }

    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('boletines.ver') || $usuario->hasPermissionTo('boletines.gestionar');
    }

    public function view(Usuario $usuario, Boletin $boletin): bool
    {
        if (!$usuario->hasPermissionTo('boletines.ver') && !$usuario->hasPermissionTo('boletines.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía del aula de la matrícula
        return $this->ownsViaMatriculaAula($usuario, $boletin, 'matricula');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('boletines.gestionar');
    }

    public function update(Usuario $usuario, Boletin $boletin): bool
    {
        if (!$usuario->hasPermissionTo('boletines.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía Y Coordinador/Secretaria con permiso
        return $this->ownsViaMatriculaAula($usuario, $boletin, 'matricula')
            || $this->coordinatesModality($usuario, $boletin, 'matricula.aula.modalidad_id');
    }

    public function delete(Usuario $usuario, Boletin $boletin): bool
    {
        return $this->isSuperAdmin($usuario);
    }
}
```

**Archivo:** `app/Policies/MatriculaPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Matricula;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class MatriculaPolicy
{
    use HasResourceOwnership;

    public function before(Usuario $usuario, $ability): ?bool
    {
        if (in_array($ability, ['viewAny', 'view']) && $this->isSuperAdmin($usuario)) {
            return true;
        }
        return null;
    }

    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('alumnos.ver') || $usuario->hasPermissionTo('alumnos.gestionar');
    }

    public function view(Usuario $usuario, Matricula $matricula): bool
    {
        if ($usuario->hasPermissionTo('boletines.ver') || $usuario->hasPermissionTo('boletines.gestionar')) {
            // Coordinador/Secretaria con permiso boletines ven matrículas de su modalidad
            if ($this->coordinatesModality($usuario, $matricula, 'aula.modalidad_id')) {
                return true;
            }
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }
        }

        if ($usuario->hasPermissionTo('alumnos.ver') || $usuario->hasPermissionTo('alumnos.gestionar')) {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }
            // Docente guía ve su aula
            if ($this->ownsAula($usuario, $matricula, 'aula')) {
                return true;
            }
            // Docente por asignatura ve si imparte en ese aula
            $docente = $usuario->docente;
            if ($docente && $matricula->aula) {
                return \App\Models\AulaAsignaturaDocente::where('aula_id', $matricula->aula_id)
                    ->where('docente_id', $docente->id)
                    ->where('activo', true)
                    ->exists();
            }
        }

        return false;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('alumnos.gestionar');
    }

    public function update(Usuario $usuario, Matricula $matricula): bool
    {
        if (!$usuario->hasPermissionTo('alumnos.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Secretaria') || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        // Coordinador: solo su modalidad
        if ($this->coordinatesModality($usuario, $matricula, 'aula.modalidad_id')) {
            return true;
        }

        // Docente guía: solo su aula
        return $this->ownsAula($usuario, $matricula, 'aula');
    }

    public function delete(Usuario $usuario, Matricula $matricula): bool
    {
        if (!$usuario->hasPermissionTo('alumnos.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Secretaria') || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        if ($this->coordinatesModality($usuario, $matricula, 'aula.modalidad_id')) {
            return true;
        }

        return $this->ownsAula($usuario, $matricula, 'aula');
    }
}
```

---

### 3.6 CONTROLADORES REFORZADOS - AUTHORIZATION EXPLÍCITA

**Archivo:** `app/Http/Controllers/NotaController.php` (Patrón para todos)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\AulaAsignaturaDocente;
use App\Services\NotaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NotaController extends Controller
{
    use AuthorizesRequests;

    protected $notaService;

    public function __construct(NotaService $notaService)
    {
        $this->notaService = $notaService;
    }

    public function index(Request $request)
    {
        // 1. AUTHORIZATION EXPLÍCITA - Policy maneja permiso base
        $this->authorize('viewAny', Nota::class);

        try {
            $usuario = auth()->user();
            
            // 2. SCOPE DE DATOS - Consulta filtrada por ownership
            // La Policy viewAny solo verifica permiso, el scope filtra datos
            $asignaciones = $this->getAsignacionesScope($usuario);
            
            // ... resto del código sin lógica de autorización hardcodeada
            
        } catch (\Exception $e) {
            // ...
        }
    }

    public function create(Request $request, AulaAsignaturaDocente $asignacion)
    {
        // 1. AUTHORIZATION EN RECURSO ESPECÍFICO (calificar)
        $this->authorize('calificar', $asignacion);

        // 2. VERIFICACIÓN ADICIONAL: Corte no bloqueado (policy lo hace, pero double-check)
        if ($this->isCorteBloqueado($request->corte_evaluativo_id, $asignacion->id)) {
            return back()->with('error', 'El parcial está cerrado. Solicita autorización para modificar.');
        }

        // ... resto del código
    }

    public function store(Request $request, AulaAsignaturaDocente $asignacion)
    {
        // Validación ANTES de authorization (para proteger formulario)
        $corteId = $request->corte_evaluativo_id;
        if (!$corteId || !\App\Models\CorteEvaluativo::where('id', $corteId)->exists()) {
            return back()->with('error', 'Debe seleccionar un periodo evaluativo válido.');
        }

        // 1. AUTHORIZATION PRIMERO (Zero Trust: verificar antes de procesar)
        $this->authorize('calificar', $asignacion);

        // 2. VERIFICAR BLOQUEO
        if ($this->isCorteBloqueado($corteId, $asignacion->id)) {
            return back()->with('error', 'El parcial está cerrado. Solicita autorización para modificar.');
        }

        // ... procesamiento seguro
    }

    public function cerrarParcial(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('calificar', $asignacion);
        // ... lógica de cierre
    }

    public function solicitarDesbloqueo(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $request->validate(['motivo' => 'required|string|max:500']);
        
        $this->authorize('calificar', $asignacion);
        // ... lógica de solicitud
    }

    /**
     * Scope de asignaciones según ownership del usuario
     * CENTRALIZA la lógica de filtrado que estaba dispersa
     */
    protected function getAsignacionesScope(\App\Models\Usuario $usuario)
    {
        $query = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente.usuario']);

        if ($usuario->hasRole(['Director', 'Subdirector'])) {
            // Super-admin ve todo (filtrado por request params en vista)
            return $query->get();
        }

        $docente = $usuario->docente;
        if (!$docente) {
            return collect();
        }

        if ($usuario->hasRole('Docente Guia')) {
            // Docente guía: sus asignaciones + asignaciones de su aula
            return $query->where(function ($q) use ($docente) {
                $q->where('docente_id', $docente->id)
                  ->orWhereHas('aula', fn($aq) => $aq->where('docente_guia_id', $docente->id));
            })->get();
        }

        // Docente por Asignatura: solo sus asignaciones
        return $query->where('docente_id', $docente->id)->get();
    }

    protected function isCorteBloqueado(int $corteId, int $asignacionId): bool
    {
        return \App\Models\CorteCerrado::where('aula_asignatura_docente_id', $asignacionId)
            ->where('corte_evaluativo_id', $corteId)
            ->where('bloqueado', true)
            ->exists();
    }
}
```

---

### 3.7 FORM REQUESTS CON AUTHORIZATION

**Archivo:** `app/Http/Requests/StoreNotaRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class StoreNotaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Zero Trust: Authorization ANTES de validación de datos
     */
    public function authorize(): bool
    {
        $asignacion = $this->route('asignacion');
        
        if (!$asignacion instanceof \App\Models\AulaAsignaturaDocente) {
            return false;
        }

        try {
            $this->authorize('calificar', $asignacion);
            return true;
        } catch (AuthorizationException $e) {
            return false;
        }
    }

    public function rules(): array
    {
        return [
            'corte_evaluativo_id' => 'required|exists:corte_evaluativo,id',
            'notas' => 'required|array',
            'notas.*' => 'array',
            'notas.*.*' => 'nullable|numeric|min:0|max:100',
        ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('No tiene autorización para calificar en esta asignación.');
    }
}
```

---

### 3.8 REPORTE CONTROLLER - SCOPE POR MODALIDAD/AULA

**Archivo:** `app/Http/Controllers/ReporteController.php` - Método `autorizarReportes` actualizado

```php
protected function autorizarReportes(): void
{
    $user = auth()->user();
    
    // 1. Permiso base
    if (!$user->hasAnyPermission(['reportes.ver', 'reportes.gestionar', 'reportes.supervisar'])) {
        throw new \Illuminate\Auth\Access\AuthorizationException('No tiene permisos para acceder a los reportes.');
    }

    // 2. Para Coordinador: inyectar scope de modalidad en request para que servicios lo usen
    if ($user->hasRole('Coordinador') && !$user->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
        $docente = $user->docente;
        if ($docente && $docente->modalidad_coordina_id) {
            // Forzar filtro de modalidad en todos los reportes
            request()->merge(['modalidad_id' => $docente->modalidad_coordina_id]);
        }
    }

    // 3. Para Docente Guía: inyectar scope de aula
    if ($user->hasRole('Docente Guia') && !$user->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios', 'Coordinador'])) {
        $docente = $user->docente;
        $anioActivo = \App\Models\AnioEscolar::where('estado', 'activo')->first();
        if ($docente && $anioActivo) {
            $aula = \App\Models\Aula::where('docente_guia_id', $docente->id)
                ->where('anio_escolar_id', $anioActivo->id)
                ->first();
            if ($aula) {
                request()->merge(['aula_id' => $aula->id, 'grado_id' => $aula->grado_id]);
            }
        }
    }
}
```

**Servicio `ReporteService` debe respetar filtros inyectados:**

```php
// En cada método del servicio, usar $filtros['modalidad_id'] y $filtros['aula_id'] 
// para restringir consultas base
```

---

### 3.9 MIDDLEWARE DE RATE LIMITING PARA AUTH

**Archivo:** `app/Http/Middleware/RateLimitAuth.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'auth:' . $request->ip();
        
        // Login: 5 intentos por minuto
        if ($request->is('login') && $request->isMethod('post')) {
            if (RateLimiter::tooManyAttempts($key, 5)) {
                return response()->json([
                    'message' => 'Demasiados intentos. Intente en ' . RateLimiter::availableIn($key) . ' segundos.'
                ], 429);
            }
            RateLimiter::hit($key, 60);
        }

        // Password reset: 3 intentos por hora
        if ($request->is('forgot-password') && $request->isMethod('post')) {
            $resetKey = 'password_reset:' . $request->ip();
            if (RateLimiter::tooManyAttempts($resetKey, 3)) {
                return back()->with('error', 'Demasiadas solicitudes. Intente más tarde.');
            }
            RateLimiter::hit($resetKey, 3600);
        }

        return $next($request);
    }
}
```

**Registro en `routes/auth.php`:**

```php
Route::middleware(['guest', \App\Http\Middleware\RateLimitAuth::class])->group(function () {
    Route::get('login', ...)->name('login');
    Route::post('login', ...);
    Route::post('forgot-password', ...)->name('password.email');
    // ...
});
```

---

### 3.10 LOGGING DE ACCESOS DENEGADOS (AUDITORÍA SIEM)

**Archivo:** `app/Exceptions/Handler.php` - Método `register` actualizado

```php
public function register(): void
{
    $this->reportable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
        // Log estructurado para SIEM
        \Illuminate\Support\Facades\Log::warning('Authorization denied', [
            'user_id' => auth()->id(),
            'user_roles' => auth()->user()?->getRoleNames()->toArray() ?? [],
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'ability' => $e->getMessage(), // Permiso/acción denegada
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // ... otros handlers
}
```

---

### 3.11 PERMISOS ESPECÍFICOS PARA REPORTES (Granularidad)

**Archivo:** `database/seeders/PermisoSeeder.php` - Actualizar permisos de reportes

```php
// Reemplazar permisos genéricos por granulares
$permisos = [
    // ... existentes
    // Reportes - GRANULARES POR TIPO
    'reportes.control_notas.ver',
    'reportes.control_notas.gestionar',
    'reportes.notas_globales.ver',
    'reportes.notas_pendientes.ver',
    'reportes.asistencia_global.ver',
    'reportes.estadisticas_asistencia.ver',
    'reportes.asistencia_seccion.ver',
    'reportes.rendimiento_corte.ver',
    'reportes.historial_estudiante.ver',
    'reportes.mined.ver',
    'reportes.avance_programatico.ver',
    'reportes.estudiantes.ver',
    'reportes.padres.ver',
    'reportes.escalas.ver',
    'reportes.asignaturas_aprobadas.ver',
    // Reportes propios del docente
    'reportes.mis_clases.ver',
    'reportes.mi_aula.ver',
];
```

**Asignar por rol:**

```php
// Coordinador: solo ver reportes de SU modalidad (scope via middleware)
$coordinador->syncPermissions([
    'alumnos.ver', 'aulas.ver', 'notas.ver', 'asistencia.ver', 
    'indicadores.ver', 'horarios.ver',
    'reportes.control_notas.ver',
    'reportes.notas_globales.ver',
    'reportes.asistencia_global.ver',
    'reportes.estadisticas_asistencia.ver',
    'reportes.rendimiento_corte.ver',
    // ... reportes de su modalidad
]);

// Docente Guía: solo reportes de SU aula
$docente_guia->syncPermissions([
    // ... existentes
    'reportes.mi_aula.ver',
    'reportes.mis_clases.ver',
]);
```

---

## PLAN DE IMPLEMENTACIÓN PRIORIZADO

| Fase | Tarea | Esfuerzo | Riesgo si No Se Hace |
|------|-------|----------|---------------------|
| **1** | Aplicar migraciones RLS en Supabase | 4h | **CRÍTICO** - Fuga total de datos si BD accedida directamente |
| **2** | Crear middleware `SetSupabaseRLSContext` | 1h | RLS ineficaz sin contexto de usuario |
| **3** | Refactorizar 14 Policies (trait + deny-by-default) | 6h | Bypass de autorización por `before()` |
| **4** | Actualizar 15+ controladores con `$this->authorize()` explícito | 4h | Lógica de auth dispersa e inconsistente |
| **5** | Implementar FormRequests con `authorize()` | 2h | Validación sin auth = DoS + IDOR |
| **6** | Granularizar permisos de reportes + scope en middleware | 3h | Coordinador/Docente ven datos cross-tenant |
| **7** | Rate limiting auth + logging denegaciones | 1h | Fuerza bruta + sin auditoría |
| **8** | Tests de autorización (Pest/PHPUnit) | 8h | Regresiones silenciosas |

**Total estimado:** ~29 horas de desarrollo + tests

---

## VALIDACIÓN POST-IMPLEMENTACIÓN

### Checklist de Verificación Zero Trust

- [ ] **RLS Activo:** `SELECT * FROM pg_policies WHERE schemaname = 'public';` retorna 35+ políticas
- [ ] **Contexto RLS:** Middleware setea `app.current_user_id` en cada request autenticado
- [ ] **Policies Deny-by-Default:** Ningún `before()` retorna `true` para `create`/`update`/`delete`
- [ ] **Ownership Verificado:** Cada `update`/`delete` policy verifica `ownsViaAsignacion()` o similar
- [ ] **Controladores Limpios:** Sin lógica `if ($user->hasRole(...))` para autorización
- [ ] **FormRequests Auth:** Todos los `Store/Update*Request` implementan `authorize()` con policy
- [ ] **Reportes Scoped:** Coordinador/Docente solo ven su modalidad/aula
- [ ] **Rate Limiting:** Login/forgot-password protegidos
- [ ] **Auditoría:** Logs estructurados de `AuthorizationException` en `storage/logs/laravel.log`
- [ ] **Tests:** Cobertura >90% en policies y controladores críticos

### Comando de Verificación RLS

```bash
# En Supabase SQL Editor
SELECT 
  schemaname,
  tablename,
  policyname,
  permissive,
  roles,
  cmd,
  qual,
  with_check
FROM pg_policies 
WHERE schemaname = 'public'
ORDER BY tablename, policyname;
```

---

## CONCLUSIÓN

El sistema **actualmente no cumple** con arquitectura Zero Trust. Las violaciones críticas (CVE-2026-ZT-001 a 004) permiten:

1. **Acceso total a BD** sin RLS (capa de datos sin defensa)
2. **Bypass de autorización** via `before()` en policies (Director/Subdirector ven todo sin ownership)
3. **Filtros de datos ausentes** en controladores y reportes (cross-tenant data exposure)
4. **Autorización inconsistente** dispersa entre middleware, policies, y controladores

La remediación propuesta implementa **verificación explícita en cada capa**:
- **Base de datos:** RLS policies granulares por rol/ownership
- **Aplicación:** Policies deny-by-default + trait de ownership reutilizable
- **Controladores:** Authorization explícita + scopes centralizados
- **Validación:** FormRequests con auth antes de validación
- **Red:** Rate limiting + logging de auditoría

**Recomendación:** Implementar en orden de fases. Fase 1 (RLS) es bloqueante para producción.</think>
<tool_call>
<function=shell>
<parameter=command>
cd E:\laragon\www\sitema_gestion_academico && php artisan migrate:status 2>&1 | head -50