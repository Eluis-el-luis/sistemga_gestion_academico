# Resumen de Cambios - Remediación de Vulnerabilidades de Control de Acceso

**Fecha:** 2026-11-09  
**Proyecto:** Sistema de Gestión Académico  
**Rol afectado:** Docente Guía  

---

## Archivos Modificados

### 1. `app/Http/Controllers/AulaAsignaturaController.php`
**Vulnerabilidad:** CVE-2026-001 (CRÍTICA) - Escalada de privilegios vía `asignarMasivo()`

**Cambios:**
- Línea 117: Cambiado `$this->authorize('viewAny', Aula::class)` por `$this->authorize('create', AulaAsignaturaDocente::class)`
- Líneas 120-135: Agregada validación de ownership para Docente Guía
  - Obtiene el aula del Docente Guía autenticado
  - Si no es Director/Subdirector/Gestor, fuerza `aulas_ids` a solo su propia aula
  - Retorna error si no tiene aula asignada

**Código clave:**
```php
$usuario = auth()->user();
$docente = $usuario->docente;

if ($docente && !$usuario->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
    $aulaGuia = \App\Models\Aula::where('docente_guia_id', $docente->id)
        ->whereHas('anioEscolar', fn($q) => $q->where('estado', 'activo'))
        ->first();

    if (!$aulaGuia) {
        return back()->with('error', 'No tiene un aula asignada como Guía en el ciclo actual.');
    }
    $request->merge(['aulas_ids' => [$aulaGuia->id]]);
}
```

---

### 2. `app/Http/Controllers/ActividadEvaluativaController.php`
**Vulnerabilidad:** CVE-2026-002 (ALTA) - IDOR / Missing Authorization en `index()`

**Cambios:**
- Línea 21: Agregado `$this->authorize('calificar', $asignacion);` al inicio del método `index()`

**Código clave:**
```php
public function index(Request $request, \App\Models\AulaAsignaturaDocente $asignacion)
{
    try {
        // FIX CVE-2026-002: Verificación de autorización obligatoria
        $this->authorize('calificar', $asignacion);
        // ...
    }
}
```

---

### 3. `app/Http/Controllers/AvanceContenidoController.php`
**Vulnerabilidades:** CVE-2026-003 (ALTA) - IDOR en `index()` + CVE-2026-004 (MEDIA) - Lógica de supervisión incompleta

**Cambios:**
- Línea 25: Incluido `'Docente Guia'` en roles de supervisión global
- Líneas 27-48: Reescrita lógica de obtención de asignaciones para Docente Guía
  - Ve sus materias directas (`docente_id`) + materias de su aula (`aula_id` donde es `docente_guia_id`)
- Líneas 54-60: Validación de `asignacion_id` contra colección filtrada (no `find()` global)
  - Usa `$asignaciones->firstWhere('id', $asignacionId)` en lugar de `AulaAsignaturaDocente::find()`

**Código clave:**
```php
$esSupervisorGlobal = $usuario->hasRole(['Subdirector', 'Director', 'Coordinador']);
$esDocenteGuia = $usuario->hasRole('Docente Guia');

if ($esSupervisorGlobal) {
    $asignaciones = AulaAsignaturaDocente::with(...)->get();
} elseif ($esDocenteGuia) {
    $docente = $usuario->docente;
    $aulaGuia = $docente ? Aula::where('docente_guia_id', $docente->id)->whereHas(...)->first() : null;
    
    $query = AulaAsignaturaDocente::with(...);
    if ($docente) $query->where('docente_id', $docente->id);
    if ($aulaGuia) $query->orWhere('aula_id', $aulaGuia->id);
    $asignaciones = $query->get();
}

// Validación de ownership en query parameter
$asignacionSeleccionada = $asignaciones->firstWhere('id', $asignacionId);
if (!$asignacionSeleccionada) {
    $asignacionSeleccionada = $asignaciones->first(); // fallback seguro
}
```

---

### 4. `app/Policies/AulaAsignaturaDocentePolicy.php`
**Vulnerabilidad:** CVE-2026-005 (MEDIA) - Null-safe access en `calificar()` y `gestionarAsistencia()`

**Cambios:**
- Línea 76: `$asignatura->aula->docente_guia_id` → `$asignatura->aula?->docente_guia_id`
- Línea 103: `$asignatura->aula->docente_guia_id` → `$asignatura->aula?->docente_guia_id`

**Código clave:**
```php
// Regla B: ¿Es el docente guía dueño de toda el aula?
// FIX CVE-2026-005: Null-safe access a relación aula
if ($asignatura->aula?->docente_guia_id === $docente->id) {
    return true;
}
```

---

### 5. `app/Http/Controllers/AulaController.php`
**Vulnerabilidad:** CVE-2026-006 (BAJA) - Information Disclosure en `showAsignaciones()`

**Cambios:**
- Líneas 380-387: Agregada validación de ownership para Docente Guía (igual que en método `show()`)

**Código clave:**
```php
$usuario = auth()->user();
if ($usuario->hasRole('Docente Guia') && !$usuario->hasRole(['Director', 'Subdirector'])) {
    $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
    if (!$docente || $aula->docente_guia_id !== $docente->id) {
        abort(403, 'No tiene autorización para ver las asignaciones de esta aula.');
    }
}
```

---

## Matriz de Verificación

| Vulnerabilidad | Archivo | Método | Fix Aplicado | Tests Requeridos |
|----------------|---------|--------|--------------|------------------|
| CVE-2026-001 | AulaAsignaturaController | asignarMasivo | ✅ Ownership validation | test_docente_guia_no_puede_asignar_masivo_otras_aulas |
| CVE-2026-002 | ActividadEvaluativaController | index | ✅ authorize('calificar') | test_actividad_evaluativa_index_requiere_calificar |
| CVE-2026-003 | AvanceContenidoController | index | ✅ firstWhere vs find | test_avance_contenido_filtra_por_ownership |
| CVE-2026-004 | AvanceContenidoController | index | ✅ Docente Guía en supervisión | test_docente_guia_ve_sus_asignaturas_en_avance |
| CVE-2026-005 | AulaAsignaturaDocentePolicy | calificar, gestionarAsistencia | ✅ Null-safe operator | test_policy_null_safe |
| CVE-2026-006 | AulaController | showAsignaciones | ✅ Ownership check | test_show_asignaciones_ownership |

---

## Permisos Requeridos por Rol (Post-Fix)

| Acción | Director | Subdirector | Coordinador | **Docente Guía** | Docente Asignatura | Gestor Usuarios |
|--------|----------|-------------|-------------|------------------|-------------------|-----------------|
| Ver TODAS asignaciones | ✅ | ✅ | ✅ | ❌ (solo su aula) | ❌ (solo sus materias) | ✅ |
| Asignar docentes (masivo) | ✅ | ✅ | ✅ | ⚠️ **Solo SU aula** | ❌ | ✅ |
| Ver actividades evaluativas | ✅ | ✅ | ✅ | ✅ (su aula) | ✅ (sus materias) | ❌ |
| Crear actividades evaluativas | ✅ | ✅ | ✅ | ✅ (su aula) | ✅ (sus materias) | ❌ |
| Ver avance contenidos | ✅ | ✅ | ✅ | ✅ (su aula + sus materias) | ✅ (sus materias) | ❌ |
| Registrar avance contenidos | ✅ | ✅ | ✅ | ✅ (sus materias) | ✅ (sus materias) | ❌ |

**Nota:** Docente Guía ahora tiene acceso a avance de contenidos de **todas las materias de su aula** (no solo las que imparte directamente), lo cual es el comportamiento correcto para un tutor.

---

## Pruebas Manuales Recomendadas

### Escenario 1: Docente Guía legítimo en su aula
1. Login como Docente Guía con aula asignada
2. Acceder a `/academico/asignaciones` → Ver solo su aula
3. Usar "Asignación Masiva" → Solo debe mostrar su aula
4. Acceder a `/academico/notas/actividades/{su_asignacion}` → Debe funcionar
5. Acceder a `/academico/avance-contenido` → Debe ver todas las materias de su aula

### Escenario 2: Docente Guía SIN aula asignada
1. Login como Docente Guía sin aula
2. Intentar "Asignación Masiva" → Debe mostrar error "No tiene un aula asignada"
3. Acceder a Avance de Contenidos → Lista vacía (correcto)

### Escenario 3: Docente Guía intentando acceder a aula ajena
1. Login como Docente Guía
2. Intentar `GET /academico/asignaciones/{aula_ajena}` → 403
2. Intentar `POST /asignaciones/masivo` con `aulas_ids` de otra aula → 403 / redirigido a su aula
3. Intentar `GET /academico/notas/actividades/{asignacion_ajena}` → 403
4. Intentar `GET /academico/avance-contenido?asignacion_id={id_ajeno}` → Muestra su primera asignación (no la ajena)

### Escenario 4: Director/Subdirector (supervisión global)
1. Login como Director
2. Todas las vistas deben mostrar TODAS las aulas/asignaciones
3. Asignación masiva debe funcionar para cualquier aula

---

## Rollback Plan

Si surgen problemas críticos:

```bash
# Revertir cambios específicos
git checkout app/Http/Controllers/AulaAsignaturaController.php
git checkout app/Http/Controllers/ActividadEvaluativaController.php
git checkout app/Http/Controllers/AvanceContenidoController.php
git checkout app/Policies/AulaAsignaturaDocentePolicy.php
git checkout app/Http/Controllers/AulaController.php
```

---

## Contacto

Para dudas sobre la implementación de estos fixes, consultar al equipo de seguridad o revisar el informe completo en `SECURITY_AUDIT_REPORT.md`.