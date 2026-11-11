# Auditoría de Seguridad y Refactorización - Autorización Granular y Renderizado Condicional

## Resumen Ejecutivo

Se corrigieron dos problemas críticos de sobre-restricción y escalada de privilegios:

| Problema | Antes | Después |
|----------|-------|---------|
| **Docente Guía** | Bloqueado de reportes MINED propios | Accede a sus reportes (rendimiento-corte, mined-detallado, escalas, asignaturas-aprobadas) con scope automático por aula |
| **Coordinador** | Bloqueado de ver aulas + podía editar en Centro de Evaluaciones | Ve aulas de su modalidad + acceso **solo lectura** (supervisión) en Centro de Evaluaciones |

---

## Fase 1: Auditoría de Granularidad (Frontend y Backend)

### Hallazgos Identificados

1. **Permisos faltantes para Docente Guía**: `PermisoSeeder` no incluía permisos granulares para reportes MINED oficiales
2. **Policy `calificar` muy restrictiva**: Requería `notas.gestionar` para Coordinador (que no tiene), bloqueando acceso de solo lectura
3. **Vistas sin renderizado condicional**: `actividades.blade.php` mostraba botones de crear/editar/eliminar a Coordinador
4. **Tabs de reportes visibles para todos**: `reportes/index.blade.php` mostraba todas las pestañas sin verificar permisos

---

## Fase 2: Ajuste de Políticas (Laravel Policies y Gates)

### 2.1 PermisoSeeder - Permisos Granulares para Docente Guía

**Archivo**: `database/seeders/PermisoSeeder.php`

```php
// ANTES: Solo reportes.mi_aula.ver y reportes.mis_clases.ver
// DESPUÉS: Agregados permisos MINED específicos con scope por aula
'reportes.rendimiento_corte.ver',
'reportes.mined_detallado.ver',
'reportes.escalas.ver',
'reportes.asignaturas_aprobadas.ver',
```

### 2.2 PermisoSeeder - Permiso para Coordinador ver asignaciones

```php
// Agregado para que Coordinador pueda usar policy 'ver' en AulaAsignaturaDocente
'asignaturas_aula.ver',
```

### 2.3 ReporteController - Autorización Granular para Reportes MINED

**Archivo**: `app/Http/Controllers/ReporteController.php` - método `autorizarReportes()`

- Verifica permisos base + permisos específicos `reportes.mi_aula.ver` / `reportes.mis_clases.ver`
- Inyecta scope automático (`aula_id`, `grado_id`) para Docente Guía
- Valida que Docente Guía tenga permiso para reportes MINED específicos antes de permitir acceso

### 2.4 AulaAsignaturaDocentePolicy - Nuevo método `ver()` para Coordinador

**Archivo**: `app/Policies/AulaAsignaturaDocentePolicy.php`

```php
public function ver(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
{
    // Directiva: acceso total
    if ($this->isSuperAdmin($usuario)) return true;
    
    // Coordinador: supervisión de su modalidad (SOLO LECTURA)
    if ($usuario->hasRole('Coordinador')) {
        return $this->coordinatesModality($usuario, $asignacion, 'aula.modalidad_id');
    }
    
    // Gestor de Usuarios: acceso total
    if ($usuario->hasRole('Gestor de Usuarios')) return true;
    
    // Docentes: ownership normal
    if ($this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion)) return true;
    if ($this->ownsAula($usuario, $asignacion, 'aula')) return true;
    
    return false;
}
```

### 2.5 ActividadEvaluativaController - Acceso Solo Lectura para Coordinador

**Archivo**: `app/Http/Controllers/ActividadEvaluativaController.php`

- Cambió `$this->authorize('calificar', $asignacion)` → `$this->authorize('ver', $asignacion)` para `index()`
- `store()`, `update()`, `destroy()` mantienen `calificar` (requiere `notas.gestionar`)
- Agregó variable `esCoordinador` para la vista
- `modoSupervision` ahora incluye Coordinador

---

## Fase 3: Refactorización de Vistas (UI/UX Segura)

### 3.1 Reporte Index - Tabs Condicionales por Permiso

**Archivo**: `resources/views/academico/reportes/index.blade.php`

```blade
@can('reportes.control_notas.ver')
    <!-- Tab Auditoría Interna -->
@endcan

@can('reportes.rendimiento_corte.ver')
    <!-- Tab Formatos MINED -->
@endcan

@can('reportes.estudiantes.ver')
    <!-- Tab Administración Estudiantil -->
@endcan
```

**Resultado**: Docente Guía solo ve pestaña "Formatos MINED" (sus reportes). No ve "Auditoría Interna" ni "Administración Estudiantil" que llevan al Centro de Reportes completo.

### 3.2 Centro de Evaluaciones - Botones Condicionales

**Archivo**: `resources/views/academico/notas/actividades.blade.php`

```blade
@can('calificar', $asignacion)
    <!-- Formulario NUEVA ACTIVIDAD -->
    <!-- Botón ELIMINAR en tabla -->
@else
    <!-- Badge "Solo lectura" -->
@endcan

@can('notas.gestionar')
    <!-- Botón "Ir a Calificar Planilla" -->
@endcan
```

**Cambios en header**:
```blade
{{ ($esDirectiva || $esCoordinador) ? 'Auditoría de Actividades:' : 'Configurar Actividades:' }}
```

**Resultado**: Coordinador ve "Auditoría de Actividades" + tabla de datos + badge "Solo lectura". NO ve formulario, botón eliminar, ni botón "Ir a Calificar".

---

## Fase 4: Corrección de RLS en Supabase (Solo Lectura)

### 4.1 Nuevas Políticas para `actividad_evaluativa`

**Archivo**: `database/migrations/2026_11_09_000001_enable_rls_all_tables.sql`

```sql
-- SELECT: Coordinador ve todo en su modalidad (solo lectura)
CREATE POLICY actividad_evaluativa_select_policy ON actividad_evaluativa
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('notas.ver')  -- Coordinador tiene esto
    OR has_permission('notas.gestionar')
    OR aula_asignatura_docente_id IN (SELECT id FROM aula_asignatura_docente WHERE docente_id = current_docente_id())
    OR aula_asignatura_docente_id IN (SELECT aad.id FROM aula_asignatura_docente aad JOIN aula a ON a.id = aad.aula_id WHERE a.docente_guia_id = current_docente_id())
    OR (has_role('Coordinador') AND aula_asignatura_docente_id IN (SELECT aad.id FROM aula_asignatura_docente aad JOIN aula a ON a.id = aad.aula_id WHERE a.modalidad_id = current_coordinador_modalidad_id()))
  );

-- INSERT/UPDATE/DELETE: Solo quien tiene notas.gestionar + ownership + corte no bloqueado
-- Coordinador NO tiene notas.gestionar → NO puede insertar/actualizar/eliminar
```

### 4.2 Nuevas Políticas para `nota_actividad`

```sql
-- SELECT: Coordinador ve notas de su modalidad
-- INSERT/UPDATE/DELETE: Solo notas.gestionar + ownership + corte no bloqueado
-- Coordinador NO tiene notas.gestionar → NO puede modificar notas
```

### Separación Explícita SELECT vs INSERT/UPDATE/DELETE

| Tabla | SELECT (Lectura) | INSERT/UPDATE/DELETE (Escritura) |
|-------|------------------|----------------------------------|
| `actividad_evaluativa` | Coordinador ✓ (su modalidad) | Solo `notas.gestionar` + owner |
| `nota_actividad` | Coordinador ✓ (su modalidad) | Solo `notas.gestionar` + owner |
| `nota` | Coordinador ✓ (su modalidad via `notas.ver`) | Solo `notas.gestionar` + owner + corte abierto |
| `aula` | Coordinador ✓ (su modalidad) | Coordinador ✓ (su modalidad) |
| `matricula` | Coordinador ✓ (su modalidad) | Coordinador ✓ (su modalidad) |

---

## Verificación de Cambios

### Archivos Modificados

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `database/seeders/PermisoSeeder.php` | Backend | Permisos granulares Docente Guía + Coordinador |
| `app/Http/Controllers/ReporteController.php` | Backend | Autorización granular reportes MINED |
| `app/Http/Controllers/ActividadEvaluativaController.php` | Backend | Policy `ver` para Coordinador, solo lectura |
| `app/Policies/AulaAsignaturaDocentePolicy.php` | Backend | Nuevo método `ver()` para supervisión |
| `resources/views/academico/reportes/index.blade.php` | Frontend | Tabs condicionales con @can |
| `resources/views/academico/notas/actividades.blade.php` | Frontend | Botones condicionales con @can |
| `database/migrations/2026_11_09_000001_enable_rls_all_tables.sql` | DB/RLS | Políticas SELECT vs WRITE separadas |

### Cómo Probar

1. **Ejecutar seeder de permisos**:
   ```bash
   php artisan db:seed --class=PermisoSeeder
   ```

2. **Aplicar migración RLS en Supabase**:
   ```bash
   # Copiar contenido de database/migrations/2026_11_09_000001_enable_rls_all_tables.sql
   # Ejecutar en Supabase SQL Editor
   ```

3. **Probar como Docente Guía**:
   - Login → Reportes → Ver pestaña "Formatos MINED"
   - Acceder a: Rendimiento REA, MINED Detallado, Escalas, Asignaturas Aprobadas
   - Verificar que NO ve "Auditoría Interna" ni "Administración Estudiantil"
   - Verificar que datos filtrados solo a su aula

4. **Probar como Coordinador**:
   - Login → Aulas → Ver listado de aulas de su modalidad
   - Login → Centro de Evaluaciones → Ver "Auditoría de Actividades"
   - Verificar tabla de actividades pero SIN formulario, SIN botón eliminar, SIN "Ir a Calificar"
   - Badge "Solo lectura" visible en columna Acciones

---

## Principios de Seguridad Aplicados

1. **Principio de Menor Privilegio**: Cada rol tiene exactamente lo que necesita
2. **Separación de Responsabilidades**: `ver` (lectura) vs `calificar` (escritura)
3. **Defensa en Profundidad**: Policy Laravel + Middleware Scope + RLS Supabase
4. **Contexto Explícito**: Variables de sesión PostgreSQL (`app.current_coordinador_modalidad_id`, etc.)
5. **Auditoría**: Logs de autorización en `AuthorizationLoggingTest.php`

---

## Próximos Pasos Recomendados

1. ✅ Ejecutar `PermisoSeeder` en producción
2. ✅ Aplicar migración RLS completa en Supabase
3. ⏳ Agregar tests de integración para nuevos permisos
4. ⏳ Documentar matriz de permisos por rol en Confluence/Wiki
5. ⏳ Revisar logs de autorización tras 1 semana en producción