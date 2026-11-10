# Informe de Auditoría de Seguridad - Sistema de Gestión Académico

**Fecha:** 2026-11-09  
**Alcance:** Control de acceso basado en roles (RBAC) - Rol "Docente Guía"  
**Metodología:** Análisis estático de código, revisión de políticas, middlewares, controladores y seeders de permisos

---

## Resumen Ejecutivo

Se identificaron **6 vulnerabilidades** en el sistema de autorización, de las cuales **1 es CRÍTICA** y permite escalada de privilegios completa para el rol "Docente Guía", permitiéndole gestionar asignaturas, evaluaciones y contenidos de **TODAS las aulas del sistema**, no solo la suya.

---

## Hallazgos Detallados

### 🔴 CRÍTICO: CVE-2026-001 - Escalada de Privilegios vía `asignarMasivo()`

**Archivo:** `app/Http/Controllers/AulaAsignaturaController.php` (línea 117)  
**Método:** `asignarMasivo()`  
**Tipo:** Broken Access Control / Privilege Escalation  

**Descripción:**
El método `asignarMasivo()` usa `$this->authorize('viewAny', Aula::class)` que solo requiere el permiso `aulas.ver`. El rol **Docente Guía** tiene este permiso (ver `PermisoSeeder.php` línea 82), pero **NO tiene** `aulas.gestionar`.

Esto permite a cualquier Docente Guía:
1. Acceder a `/academico/asignaciones` (indexAsignaciones - solo `viewAny`)
2. Usar la función "Asignación Masiva" para asignarse a sí mismo (o a cualquier docente) como profesor de **cualquier asignatura en CUALQUIER aula**
3. Al convertirse en `docente_id` en `AulaAsignaturaDocente`, obtiene acceso automático a:
   - Crear/modificar actividades evaluativas (`calificar` policy - Regla A)
   - Registrar avance de contenidos (controller check - `docente_id`)
   - Calificar notas
   - Gestionar horarios

**Evidencia de código vulnerable:**
```php
// Línea 117 - SOLO verifica permiso de LECTURA
$this->authorize('viewAny', Aula::class);

// Líneas 139-146 - PERO realiza ESCRITURA (CREATE)
$asignacion = AulaAsignaturaDocente::create([
    'aula_id'         => $aula->id,           // Cualquier aula del sistema
    'asignatura_id'   => $asignaturaId,       // Cualquier asignatura
    'docente_id'      => $docenteId,          // Cualquier docente (incluyendo a sí mismo)
    // ...
]);
```

**Impacto:** **Compromiso total del sistema académico** - Un Docente Guía puede tomar control de todas las asignaturas de todas las aulas.

---

### 🟠 ALTO: CVE-2026-002 - IDOR / Divulgación de Información en `ActividadEvaluativaController::index()`

**Archivo:** `app/Http/Controllers/ActividadEvaluativaController.php` (línea 16)  
**Método:** `index()`  
**Tipo:** Insecure Direct Object Reference (IDOR) / Missing Authorization  

**Descripción:**
El método `index()` **no tiene ninguna verificación de autorización** (`$this->authorize()`). Cualquier usuario autenticado con permiso `asignaturas_aula.ver` (incluye Docente Guía, Docente por Asignatura, Coordinador, Secretaría) puede acceder a:

```
GET /academico/notas/actividades/{asignacion_id}
```

Para **CUALQUIER** `AulaAsignaturaDocente` del sistema, viendo:
- Estructura de evaluaciones (actividades, tipos, puntajes)
- Pesos de corte (acumulado vs examen)
- Nombres de docentes y aulas

**Evidencia:**
```php
public function index(Request $request, \App\Models\AulaAsignaturaDocente $asignacion)
{
    // NO HAY $this->authorize() AQUÍ
    $user = auth()->user();
    $modoSupervision = $user->hasAnyRole(['Director', 'Subdirector']);
    // ... carga datos sin verificar ownership
}
```

**Impacto:** Divulgación de información académica sensible + enumeración de IDs válidos para ataques encadenados.

---

### 🟠 ALTO: CVE-2026-003 - Divulgación de Información en `AvanceContenidoController::index()`

**Archivo:** `app/Http/Controllers/AvanceContenidoController.php` (línea 42)  
**Método:** `index()`  
**Tipo:** IDOR / Broken Access Control  

**Descripción:**
El controlador filtra correctamente las asignaciones iniciales (línea 30-31), pero luego permite acceso directo a **cualquier** asignación vía query parameter `asignacion_id`:

```php
// Línea 39-42: Obtiene asignacion_id del request SIN validar ownership
$asignacionId = $request->query('asignacion_id', $asignaciones->first()->id ?? null);

if ($asignacionId) {
    // find() busca en TODA la tabla, no en $asignaciones filtrado
    $asignacionSeleccionada = AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])->find($asignacionId);
    // ...
}
```

Un Docente Guía puede manipular `?asignacion_id=X` para ver el avance de contenidos de cualquier asignatura del sistema.

---

### 🟡 MEDIO: CVE-2026-004 - Docente Guía Excluido de Lista de Supervisores en Avance de Contenidos

**Archivo:** `app/Http/Controllers/AvanceContenidoController.php` (línea 25)  
**Método:** `index()`  
**Tipo:** Logic Error / Authorization Bypass (parcial)  

**Descripción:**
La lista de roles con "modo supervisión" (acceso a todas las asignaciones) excluye a `Docente Guía`:

```php
if ($usuario->hasRole(['Subdirector', 'Director', 'Coordinador'])) {  // Falta 'Docente Guía'
    $asignaciones = AulaAsignaturaDocente::with(...)->get();  // Todas
} else {
    // Solo donde docente_id = usuario (NO incluye aula->docente_guia_id)
    $asignaciones = AulaAsignaturaDocente::where('docente_id', $docente->id)->get();
}
```

**Consecuencia:** Un Docente Guía que **no imparte ninguna materia** (solo guía aula) ve **lista vacía** en el panel de Avance de Contenidos, aunque debería ver las asignaturas de su aula.

---

### 🟡 MEDIO: CVE-2026-005 - Falta de Validación Null-Safe en Policy `calificar()`

**Archivo:** `app/Policies/AulaAsignaturaDocentePolicy.php` (línea 76)  
**Método:** `calificar()`  
**Tipo:** Defensive Programming / Potential Error  

**Descripción:**
Acceso a relación sin verificación de existencia:

```php
if ($asignatura->aula->docente_guia_id === $docente->id) {  // Error si aula es null
    return true;
}
```

Si `aula_id` es null o la relación falla, lanza `ErrorException: Trying to get property 'docente_guia_id' of non-object`.

---

### 🟢 BAJO: CVE-2026-006 - Divulgación de Asignaciones en `AulaController::showAsignaciones()`

**Archivo:** `app/Http/Controllers/AulaController.php` (línea 379)  
**Método:** `showAsignaciones()`  
**Tipo:** Information Disclosure  

**Descripción:**
Solo verifica `viewAny` en Aula (permiso `aulas.ver`), permitiendo a Docente Guía ver la página de asignaciones de **cualquier aula**, exponiendo:
- Lista de docentes por asignatura
- Asignaturas no asignadas disponibles
- IDs de `AulaAsignaturaDocente` (útiles para CVE-2026-002 y CVE-2026-003)

---

## Matriz de Riesgo por Rol

| Vulnerabilidad | Director | Subdirector | Coordinador | **Docente Guía** | Docente Asignatura | Gestor | Secretaría |
|----------------|----------|-------------|-------------|------------------|-------------------|--------|------------|
| CVE-2026-001 (asignarMasivo) | ✅ OK (tiene gestionar) | ✅ OK | ✅ OK (tiene gestionar) | **🔴 EXPLOTABLE** | ❌ Sin permiso viewAny | ✅ OK | ❌ Sin permiso viewAny |
| CVE-2026-002 (index actividades) | ✅ OK (supervisión) | ✅ OK | ✅ OK (ver) | **🟠 EXPLOTABLE** | **🟠 EXPLOTABLE** | ❌ Sin permiso | **🟠 EXPLOTABLE** |
| CVE-2026-003 (index avance) | ✅ OK (supervisión) | ✅ OK | ✅ OK | **🟠 EXPLOTABLE** | **🟠 EXPLOTABLE** | ❌ Sin permiso | ❌ Sin permiso |
| CVE-2026-004 (lógica avance) | N/A | N/A | N/A | **🟡 AFECTADO** | N/A | N/A | N/A |
| CVE-2026-005 (policy null) | N/A | N/A | N/A | **🟡 RIESGO** | **🟡 RIESGO** | N/A | N/A |
| CVE-2026-006 (showAsignaciones) | ✅ OK | ✅ OK | ✅ OK | **🟢 EXPLOTABLE** | ❌ Sin permiso | ✅ OK | **🟢 EXPLOTABLE** |

---

## Plan de Remediación

### Prioridad 1 - CRÍTICA (Inmediata)

#### Fix CVE-2026-001: `AulaAsignaturaController::asignarMasivo()`

**Cambio:** Requerir permiso de gestión (`asignaturas_aula.gestionar` o `aulas.gestionar`) en lugar de solo vista.

```php
// ANTES (línea 117):
$this->authorize('viewAny', Aula::class);

// DESPUÉS:
$this->authorize('create', AulaAsignaturaDocente::class);
// O alternativamente, verificar permiso específico:
// if (!auth()->user()->hasPermissionTo('asignaturas_aula.gestionar')) {
//     abort(403, 'No tiene permisos para realizar asignaciones masivas.');
// }
```

**Nota:** El permiso `asignaturas_aula.gestionar` lo tienen: Director, Subdirector, Gestor de Usuarios, **Docente Guía**. 

**¡ESPERA!** El Docente Guía **SÍ tiene** `asignaturas_aula.gestionar` (ver PermisoSeeder línea 85). Esto significa que incluso con este fix, el Docente Guía podría seguir usando `asignarMasivo`.

**Solución real:** El Docente Guía solo debe poder asignar docentes a las asignaturas de **SU PROPIA AULA**. Necesitamos validación de ownership:

```php
public function asignarMasivo(Request $request)
{
    $request->validate([...]);
    
    // Verificar permiso base
    $this->authorize('create', AulaAsignaturaDocente::class);
    
    $usuario = auth()->user();
    $docente = $usuario->docente;
    
    // Si es Docente Guía (no Director/Subdirector/Gestor), restringir a SU aula
    if ($docente && !$usuario->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
        $aulaGuia = \App\Models\Aula::where('docente_guia_id', $docente->id)
            ->whereHas('anioEscolar', fn($q) => $q->where('estado', 'activo'))
            ->first();
        
        if (!$aulaGuia) {
            return back()->with('error', 'No tiene un aula asignada como Guía en el ciclo actual.');
        }
        
        // Forzar que solo pueda operar sobre su propia aula
        $request->merge(['aulas_ids' => [$aulaGuia->id]]);
    }
    
    // ... resto del método
}
```

---

### Prioridad 2 - ALTA (Esta semana)

#### Fix CVE-2026-002: `ActividadEvaluativaController::index()`

**Cambio:** Añadir verificación de autorización usando policy `calificar` (que ya valida ownership correctamente).

```php
public function index(Request $request, \App\Models\AulaAsignaturaDocente $asignacion)
{
    try {
        // AGREGAR: Verificación de autorización
        $this->authorize('calificar', $asignacion);
        
        // ... resto del método
    }
}
```

**Justificación:** La policy `calificar` ya implementa correctamente:
- Regla A: ¿Es el docente que imparte la materia? (`docente_id`)
- Regla B: ¿Es el Docente Guía del aula? (`aula->docente_guia_id`)

---

#### Fix CVE-2026-003: `AvanceContenidoController::index()`

**Cambio:** Validar que el `asignacion_id` solicitado pertenezca a las asignaciones permitidas para el usuario.

```php
$asignacionId = $request->query('asignacion_id', $asignaciones->first()->id ?? null);

if ($asignacionId) {
    // VALIDAR: El asignacion_id debe estar en la colección $asignaciones (ya filtrada)
    $asignacionSeleccionada = $asignaciones->firstWhere('id', $asignacionId);
    
    if (!$asignacionSeleccionada) {
        // Si no está en sus asignaciones permitidas, usar la primera por defecto
        $asignacionSeleccionada = $asignaciones->first();
        $asignacionId = $asignacionSeleccionada?->id;
    }
    
    if ($asignacionSeleccionada) {
        $avances = AvanceContenido::where('aula_asignatura_docente_id', $asignacionId)
            ->orderBy('mes')->get();
    }
}
```

---

### Prioridad 3 - MEDIA (Próximo sprint)

#### Fix CVE-2026-004: Incluir Docente Guía en Modo Supervisión (AvanceContenido)

**Cambio:** Línea 25 en `AvanceContenidoController.php`

```php
// ANTES:
if ($usuario->hasRole(['Subdirector', 'Director', 'Coordinador'])) {

// DESPUÉS:
if ($usuario->hasRole(['Subdirector', 'Director', 'Coordinador', 'Docente Guia'])) {
```

**Y corregir la query para Docente Guía** (líneas 29-33):
```php
} else {
    $docente = $usuario->docente;
    if ($docente) {
        // Docente Guía ve: sus materias (docente_id) + materias de su aula (docente_guia_id)
        $aulaGuia = \App\Models\Aula::where('docente_guia_id', $docente->id)
            ->whereHas('anioEscolar', fn($q) => $q->where('estado', 'activo'))
            ->first();
        
        $query = AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])
            ->where('docente_id', $docente->id);
            
        if ($aulaGuia) {
            $query->orWhere(function($q) use ($aulaGuia) {
                $q->where('aula_id', $aulaGuia->id);
            });
        }
        
        $asignaciones = $query->get();
    } else {
        $asignaciones = collect();
    }
    $modoSupervision = false;
}
```

---

#### Fix CVE-2026-005: Null-Safe Policy `calificar()`

**Cambio:** `app/Policies/AulaAsignaturaDocentePolicy.php` línea 76

```php
// ANTES:
if ($asignatura->aula->docente_guia_id === $docente->id) {

// DESPUÉS (usando optional o null-safe operator):
if ($asignatura->aula?->docente_guia_id === $docente->id) {

// O más explícito:
if ($asignatura->aula && $asignatura->aula->docente_guia_id === $docente->id) {
```

---

### Prioridad 4 - BAJA (Mejora continua)

#### Fix CVE-2026-006: Restringir `AulaController::showAsignaciones()`

**Cambio:** Añadir verificación de ownership para Docente Guía (similar a `AulaController::show()` líneas 80-91).

```php
public function showAsignaciones(Aula $aula)
{
    try {
        $this->authorize('viewAny', Aula::class);
        
        // AGREGAR: Verificación de ownership para Docente Guía
        $usuario = auth()->user();
        if ($usuario->hasRole('Docente Guia') && !$usuario->hasRole(['Director', 'Subdirector'])) {
            $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
            if (!$docente || $aula->docente_guia_id !== $docente->id) {
                abort(403, 'No tiene autorización para ver las asignaciones de esta aula.');
            }
        }
        
        // ... resto del método
    }
}
```

---

## Pruebas de Regresión Requeridas

### Test Case 1: Docente Guía NO puede usar asignarMasivo para otras aulas
```php
public function test_docente_guia_no_puede_asignar_masivo_otras_aulas()
{
    $guia = $this->crearUsuario('Docente Guia');
    $otraAula = Aula::factory()->create(['docente_guia_id' => $otroDocente->id]);
    
    $this->actingAs($guia)
        ->post(route('asignaciones.masivo'), [
            'asignatura_id' => $asignatura->id,
            'docente_id' => $guia->docente->id,
            'aulas_ids' => [$otraAula->id],  // Aula de OTRO guía
        ])
        ->assertStatus(403);  // Debe fallar
}
```

### Test Case 2: Docente Guía SÍ puede usar asignarMasivo para SU aula
```php
public function test_docente_guia_puede_asignar_masivo_su_aula()
{
    $guia = $this->crearUsuario('Docente Guia');
    $suAula = Aula::factory()->create(['docente_guia_id' => $guia->docente->id]);
    
    $this->actingAs($guia)
        ->post(route('asignaciones.masivo'), [
            'asignatura_id' => $asignatura->id,
            'docente_id' => $guia->docente->id,
            'aulas_ids' => [$suAula->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
}
```

### Test Case 3: ActividadEvaluativaController::index() requiere autorización
```php
public function test_actividad_evaluativa_index_requiere_calificar()
{
    $guia = $this->crearUsuario('Docente Guia');
    $otraAsignacion = AulaAsignaturaDocente::factory()->create([
        'aula_id' => $aulaOtra->id,  // Aula de otro guía
        'docente_id' => $otroDocente->id,
    ]);
    
    $this->actingAs($guia)
        ->get(route('notas.actividades.index', $otraAsignacion))
        ->assertStatus(403);
}
```

### Test Case 4: AvanceContenido filtra por ownership
```php
public function test_avance_contenido_filtra_por_ownership()
{
    $guia = $this->crearUsuario('Docente Guia');
    $otraAsignacion = AulaAsignaturaDocente::factory()->create([
        'aula_id' => $aulaOtra->id,
        'docente_id' => $otroDocente->id,
    ]);
    
    // Intentar acceder via query parameter
    $this->actingAs($guia)
        ->get(route('avance.index', ['asignacion_id' => $otraAsignacion->id]))
        ->assertOk()
        ->assertViewHas('asignacionSeleccionada', fn($a) => $a->id !== $otraAsignacion->id);
        // Debe mostrar SU asignación, no la de otro
}
```

---

## Checklist de Verificación Post-Fix

- [ ] CVE-2026-001: `asignarMasivo()` valida ownership de aula para Docente Guía
- [ ] CVE-2026-002: `ActividadEvaluativaController::index()` tiene `$this->authorize('calificar', $asignacion)`
- [ ] CVE-2026-003: `AvanceContenidoController::index()` valida `asignacion_id` contra colección filtrada
- [ ] CVE-2026-004: Docente Guía incluido en modo supervisión y query corregida
- [ ] CVE-2026-005: Policy `calificar()` usa null-safe operator
- [ ] CVE-2026-006: `showAsignaciones()` valida ownership para Docente Guía
- [ ] Todos los tests de regresión pasan
- [ ] Tests existentes no rotos (ejecutar `php artisan test`)
- [ ] Verificar manualmente flujos críticos: Docente Guía en su aula, Director en todas

---

## Referencias

- **OWASP Top 10 2021:** A01:2021 - Broken Access Control
- **CWE-639:** Authorization Bypass Through User-Controlled Key
- **CWE-862:** Missing Authorization
- **Laravel Documentation:** Authorization - Policies
- **PermisoSeeder.php:** Definición de permisos por rol (líneas 79-89 para Docente Guía)

---

*Fin del informe. Para consultas sobre implementación de fixes, contactar al equipo de seguridad.*