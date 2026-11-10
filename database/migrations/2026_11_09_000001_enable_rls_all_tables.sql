-- ============================================================================
-- SUPABASE ROW LEVEL SECURITY (RLS) - MIGRACIÓN COMPLETA
-- ============================================================================
-- Ejecutar en Supabase SQL Editor DESPUÉS de todas las migraciones de Laravel
-- Esta migración habilita RLS en TODAS las tablas y crea políticas granulares
-- compatibles con el sistema de roles/permisos Spatie de Laravel
-- ============================================================================

-- -----------------------------------------------------------------------------
-- FUNCIONES AUXILIARES PARA CONTEXTO RLS
-- -----------------------------------------------------------------------------
-- Estas funciones leen variables de sesión PostgreSQL que Laravel setea
-- via middleware SetSupabaseRLSContext en cada request autenticado

-- Obtener user_id del usuario autenticado actual
CREATE OR REPLACE FUNCTION current_user_id() RETURNS bigint AS $$
  SELECT COALESCE(
    nullif(current_setting('app.current_user_id', true), '')::bigint,
    (nullif(current_setting('request.jwt.claims', true), '')::jsonb ->> 'sub')::bigint
  );
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Obtener roles del usuario actual como array de texto
CREATE OR REPLACE FUNCTION current_user_roles() RETURNS text[] AS $$
  SELECT COALESCE(
    nullif(current_setting('app.current_user_roles', true), ''),
    '{}'
  )::text[];
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Verificar si usuario tiene rol específico
CREATE OR REPLACE FUNCTION has_role(role_name text) RETURNS boolean AS $$
  SELECT role_name = ANY(current_user_roles());
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Verificar si usuario tiene CUALQUIERA de los roles (array)
CREATE OR REPLACE FUNCTION has_any_role(roles text[]) RETURNS boolean AS $$
  SELECT current_user_roles() && roles;
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Verificar permiso via Spatie role_has_permissions
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

-- Obtener docente_id del usuario actual
CREATE OR REPLACE FUNCTION current_docente_id() RETURNS bigint AS $$
  SELECT id FROM docente WHERE usuario_id = current_user_id() LIMIT 1;
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Verificar si es Director o Subdirector (super-admin)
CREATE OR REPLACE FUNCTION is_super_admin() RETURNS boolean AS $$
  SELECT has_any_role(ARRAY['Director','Subdirector']);
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Verificar si es Coordinador
CREATE OR REPLACE FUNCTION is_coordinador() RETURNS boolean AS $$
  SELECT has_role('Coordinador');
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Obtener modalidad que coordina el usuario actual
CREATE OR REPLACE FUNCTION current_coordinador_modalidad_id() RETURNS bigint AS $$
  SELECT modalidad_coordina_id FROM docente 
  WHERE usuario_id = current_user_id() AND es_coordinador = true LIMIT 1;
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- -----------------------------------------------------------------------------
-- TABLA: usuario
-- -----------------------------------------------------------------------------
ALTER TABLE usuario ENABLE ROW LEVEL SECURITY;

-- SELECT: Usuario ve su propio registro + roles admin ven todos
CREATE POLICY usuario_select_policy ON usuario
  FOR SELECT USING (
    id = current_user_id() 
    OR has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  );

-- INSERT: Solo roles administrativos
CREATE POLICY usuario_insert_policy ON usuario
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  );

-- UPDATE: Usuario actualiza su perfil + admins con restricciones
CREATE POLICY usuario_update_policy ON usuario
  FOR UPDATE USING (
    id = current_user_id()
    OR (
      has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
      -- Director no edita otro Director
      AND NOT (has_role('Director') AND id != current_user_id() AND has_role('Director'))
      -- Subdirector solo editable por Director/Subdirector
      AND NOT (has_role('Subdirector') AND NOT has_any_role(ARRAY['Director','Subdirector']))
    )
  ) WITH CHECK (
    id = current_user_id()
    OR (
      has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
      AND NOT (has_role('Director') AND id != current_user_id() AND has_role('Director'))
      AND NOT (has_role('Subdirector') AND NOT has_any_role(ARRAY['Director','Subdirector']))
    )
  );

-- DELETE: Nadie se auto-elimina. Director intocable. Subdirector solo por Director.
CREATE POLICY usuario_delete_policy ON usuario
  FOR DELETE USING (
    id != current_user_id()
    AND NOT has_role('Director') -- Director intocable
    AND (
      (has_role('Subdirector') AND has_role('Director'))
      OR (NOT has_role('Subdirector') AND has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios']))
    )
  );

-- -----------------------------------------------------------------------------
-- TABLA: alumno
-- -----------------------------------------------------------------------------
ALTER TABLE alumno ENABLE ROW LEVEL SECURITY;

CREATE POLICY alumno_select_policy ON alumno
  FOR SELECT USING (
    usuario_id = current_user_id() -- Alumno ve su expediente
    OR is_super_admin() -- Director/Subdirector
    OR has_role('Secretaria')
    OR has_role('Gestor de Usuarios')
    OR (
      has_role('Coordinador')
      AND EXISTS (
        SELECT 1 FROM matricula m
        JOIN aula a ON a.id = m.aula_id
        WHERE m.alumno_id = alumno.id
          AND m.estado = 'activo'
          AND a.modalidad_id = current_coordinador_modalidad_id()
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
  );

CREATE POLICY alumno_insert_policy ON alumno
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Secretaria'])
  );

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
          AND a.modalidad_id = current_coordinador_modalidad_id()
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
          AND a.modalidad_id = current_coordinador_modalidad_id()
      )
    )
  );

CREATE POLICY alumno_delete_policy ON alumno
  FOR DELETE USING ( is_super_admin() );

-- -----------------------------------------------------------------------------
-- TABLA: docente
-- -----------------------------------------------------------------------------
ALTER TABLE docente ENABLE ROW LEVEL SECURITY;

CREATE POLICY docente_select_policy ON docente
  FOR SELECT USING (
    usuario_id = current_user_id()
    OR is_super_admin()
    OR has_any_role(ARRAY['Gestor de Usuarios','Secretaria','Coordinador'])
  );

CREATE POLICY docente_insert_policy ON docente
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  );

CREATE POLICY docente_update_policy ON docente
  FOR UPDATE USING (
    usuario_id = current_user_id()
    OR has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  ) WITH CHECK (
    usuario_id = current_user_id()
    OR has_any_role(ARRAY['Director','Subdirector','Gestor de Usuarios','Secretaria'])
  );

CREATE POLICY docente_delete_policy ON docente
  FOR DELETE USING ( is_super_admin() );

-- -----------------------------------------------------------------------------
-- TABLA: aula
-- -----------------------------------------------------------------------------
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
      AND modalidad_id = current_coordinador_modalidad_id()
    )
  ) WITH CHECK (
    is_super_admin()
    OR has_role('Gestor de Usuarios')
    OR (
      has_role('Coordinador')
      AND modalidad_id = current_coordinador_modalidad_id()
    )
  );

CREATE POLICY aula_delete_policy ON aula
  FOR DELETE USING (
    is_super_admin()
    OR (
      has_role('Coordinador')
      AND modalidad_id = current_coordinador_modalidad_id()
    )
  );

-- -----------------------------------------------------------------------------
-- TABLA: matricula
-- -----------------------------------------------------------------------------
ALTER TABLE matricula ENABLE ROW LEVEL SECURITY;

CREATE POLICY matricula_select_policy ON matricula
  FOR SELECT USING (
    is_super_admin()
    OR has_any_role(ARRAY['Secretaria','Gestor de Usuarios'])
    OR (
      has_role('Coordinador')
      AND aula_id IN (
        SELECT id FROM aula 
        WHERE modalidad_id = current_coordinador_modalidad_id()
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
    OR alumno_id IN (SELECT id FROM alumno WHERE usuario_id = current_user_id())
  );

CREATE POLICY matricula_insert_policy ON matricula
  FOR INSERT WITH CHECK (
    has_any_role(ARRAY['Director','Subdirector','Secretaria','Gestor de Usuarios','Docente Guia'])
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
        WHERE modalidad_id = current_coordinador_modalidad_id()
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
        WHERE modalidad_id = current_coordinador_modalidad_id()
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
        WHERE modalidad_id = current_coordinador_modalidad_id()
      )
    )
    OR (
      has_role('Docente Guia')
      AND aula_id IN (SELECT id FROM aula WHERE docente_guia_id = current_docente_id())
    )
  );

-- -----------------------------------------------------------------------------
-- TABLA: nota
-- -----------------------------------------------------------------------------
ALTER TABLE nota ENABLE ROW LEVEL SECURITY;

CREATE POLICY nota_select_policy ON nota
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('notas.ver')
    OR has_permission('notas.gestionar')
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
  FOR DELETE USING ( is_super_admin() );

-- -----------------------------------------------------------------------------
-- TABLA: boletin
-- -----------------------------------------------------------------------------
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

-- -----------------------------------------------------------------------------
-- TABLA: aula_asignatura_docente
-- -----------------------------------------------------------------------------
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
        WHERE modalidad_id = current_coordinador_modalidad_id()
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
          WHERE modalidad_id = current_coordinador_modalidad_id()
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
          WHERE modalidad_id = current_coordinador_modalidad_id()
        )
      )
      OR docente_id = current_docente_id()
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
          WHERE modalidad_id = current_coordinador_modalidad_id()
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
          WHERE modalidad_id = current_coordinador_modalidad_id()
        )
      )
    )
  );

-- -----------------------------------------------------------------------------
-- TABLA: horario
-- -----------------------------------------------------------------------------
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

-- -----------------------------------------------------------------------------
-- TABLA: asistencia_aula
-- -----------------------------------------------------------------------------
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

-- -----------------------------------------------------------------------------
-- TABLA: corte_evaluativo
-- -----------------------------------------------------------------------------
ALTER TABLE corte_evaluativo ENABLE ROW LEVEL SECURITY;

CREATE POLICY corte_select_policy ON corte_evaluativo
  FOR SELECT USING (
    is_super_admin()
    OR has_permission('configuracion.ver')
    OR has_permission('notas.ver')
    OR has_permission('asistencia.ver')
  );

CREATE POLICY corte_insert_policy ON corte_evaluativo
  FOR INSERT WITH CHECK ( has_permission('configuracion.gestionar') );

CREATE POLICY corte_update_policy ON corte_evaluativo
  FOR UPDATE USING (
    has_permission('configuracion.gestionar')
    AND NOT es_cerrado
  ) WITH CHECK (
    has_permission('configuracion.gestionar')
    AND NOT es_cerrado
  );

CREATE POLICY corte_delete_policy ON corte_evaluativo
  FOR DELETE USING ( has_role('Director') AND es_planificacion );

-- -----------------------------------------------------------------------------
-- TABLA: anio_escolar
-- -----------------------------------------------------------------------------
ALTER TABLE anio_escolar ENABLE ROW LEVEL SECURITY;

CREATE POLICY anio_select_policy ON anio_escolar
  FOR SELECT USING ( has_permission('configuracion.ver') OR has_permission('configuracion.gestionar') );

CREATE POLICY anio_insert_policy ON anio_escolar
  FOR INSERT WITH CHECK ( has_permission('configuracion.gestionar') );

CREATE POLICY anio_update_policy ON anio_escolar
  FOR UPDATE USING (
    has_permission('configuracion.gestionar')
    AND NOT es_cerrado
  ) WITH CHECK (
    has_permission('configuracion.gestionar')
    AND NOT es_cerrado
  );

CREATE POLICY anio_delete_policy ON anio_escolar
  FOR DELETE USING ( has_role('Director') AND es_planificacion );

-- -----------------------------------------------------------------------------
-- TABLAS DE CATÁLOGO (solo lectura para roles operativos, escritura para admin)
-- -----------------------------------------------------------------------------
-- grado, modalidad, asignatura, grupo_materia, bloque_horario, indicador_logro,
-- malla_curricular, corte_cerrado, solicitud_edicion_nota, examen_reparacion,
-- avance_contenido, apoyo_padres, evaluacion_familiar, incidencia_disciplinaria,
-- asistencia_personal, envio_boletines, aviso, respaldo

DO $$
DECLARE
    t text;
BEGIN
    FOR t IN SELECT unnest(ARRAY[
        'grado','modalidad','asignatura','grupo_materia','bloque_horario',
        'indicador_logro','malla_curricular','corte_cerrado','solicitud_edicion_nota',
        'examen_reparacion','avance_contenido','apoyo_padres','evaluacion_familiar',
        'incidencia_disciplinaria','asistencia_personal','envio_boletines','aviso','respaldo'
    ])
    LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
        EXECUTE format('
            CREATE POLICY %I_select_policy ON %I
            FOR SELECT USING (
                has_permission(''configuracion.ver'') OR has_permission(''configuracion.gestionar'')
                OR has_any_role(ARRAY[''Coordinador'',''Secretaria'',''Gestor de Usuarios'',''Docente Guia'',''Docente por Asignatura''])
            )', t, t);
        EXECUTE format('
            CREATE POLICY %I_modify_policy ON %I
            FOR ALL USING ( has_permission(''configuracion.gestionar'') )', t, t);
    END LOOP;
END $$;

-- Excepción: respaldo solo Director/Subdirector
DROP POLICY IF EXISTS respaldo_select_policy ON respaldo;
DROP POLICY IF EXISTS respaldo_modify_policy ON respaldo;
CREATE POLICY respaldo_select_policy ON respaldo
  FOR SELECT USING ( has_any_role(ARRAY['Director','Subdirector']) );
CREATE POLICY respaldo_insert_policy ON respaldo
  FOR INSERT WITH CHECK ( has_any_role(ARRAY['Director','Subdirector']) );
CREATE POLICY respaldo_delete_policy ON respaldo
  FOR DELETE USING ( has_role('Director') );

-- -----------------------------------------------------------------------------
-- VERIFICACIÓN POST-MIGRACIÓN
-- -----------------------------------------------------------------------------
-- Ejecutar en Supabase SQL Editor para confirmar:
-- SELECT schemaname, tablename, policyname, cmd 
-- FROM pg_policies WHERE schemaname = 'public' ORDER BY tablename, policyname;