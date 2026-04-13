SET @schema_name = DATABASE();

-- Uniques ya presentes en el esquema inspeccionado:
-- items_biblioteca(codigo)
-- usuarios(cedula)
-- usuarios(email)
-- categorias(tipo_id, nombre)

-- items_biblioteca: catalogos, conteos por tipo y generacion de codigos.
SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'items_biblioteca'
      AND index_name = 'idx_items_tipo_deleted_id'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE items_biblioteca ADD INDEX idx_items_tipo_deleted_id (tipo_id, deleted_at, id)',
    'SELECT ''skip idx_items_tipo_deleted_id'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'items_biblioteca'
      AND index_name = 'idx_items_tipo_estado_deleted_id'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE items_biblioteca ADD INDEX idx_items_tipo_estado_deleted_id (tipo_id, estado, deleted_at, id)',
    'SELECT ''skip idx_items_tipo_estado_deleted_id'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'items_biblioteca'
      AND index_name = 'idx_items_tipo_categoria_anio_deleted'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE items_biblioteca ADD INDEX idx_items_tipo_categoria_anio_deleted (tipo_id, categoria_id, anio, deleted_at)',
    'SELECT ''skip idx_items_tipo_categoria_anio_deleted'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- visitas_items: rankings por rango de fechas y item.
SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'visitas_items'
      AND index_name = 'idx_visitas_item_fecha'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE visitas_items ADD INDEX idx_visitas_item_fecha (item_id, fecha_visita)',
    'SELECT ''skip idx_visitas_item_fecha'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- solicitudes_prestamo: listados admin/estudiante, dashboards y filtros por fecha.
SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'solicitudes_prestamo'
      AND index_name = 'idx_sp_estado_fecha_solicitud'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE solicitudes_prestamo ADD INDEX idx_sp_estado_fecha_solicitud (estado, fecha_solicitud)',
    'SELECT ''skip idx_sp_estado_fecha_solicitud'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'solicitudes_prestamo'
      AND index_name = 'idx_sp_usuario_fecha_solicitud'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE solicitudes_prestamo ADD INDEX idx_sp_usuario_fecha_solicitud (usuario_id, fecha_solicitud)',
    'SELECT ''skip idx_sp_usuario_fecha_solicitud'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'solicitudes_prestamo'
      AND index_name = 'idx_sp_fecha_prestamo_estado'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE solicitudes_prestamo ADD INDEX idx_sp_fecha_prestamo_estado (fecha_prestamo, estado)',
    'SELECT ''skip idx_sp_fecha_prestamo_estado'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'solicitudes_prestamo'
      AND index_name = 'idx_sp_fecha_respuesta_estado'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE solicitudes_prestamo ADD INDEX idx_sp_fecha_respuesta_estado (fecha_respuesta, estado)',
    'SELECT ''skip idx_sp_fecha_respuesta_estado'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'solicitudes_prestamo'
      AND index_name = 'idx_sp_estado_fecha_devolucion'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE solicitudes_prestamo ADD INDEX idx_sp_estado_fecha_devolucion (estado, fecha_devolucion)',
    'SELECT ''skip idx_sp_estado_fecha_devolucion'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- auditoria: consultas por usuario o por registro afectado.
SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'auditoria'
      AND index_name = 'idx_auditoria_usuario_created'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE auditoria ADD INDEX idx_auditoria_usuario_created (usuario_id, created_at)',
    'SELECT ''skip idx_auditoria_usuario_created'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @schema_name
      AND table_name = 'auditoria'
      AND index_name = 'idx_auditoria_tabla_registro_created'
);
SET @sql = IF(
    @exists = 0,
    'ALTER TABLE auditoria ADD INDEX idx_auditoria_tabla_registro_created (tabla_afectada, registro_id, created_at)',
    'SELECT ''skip idx_auditoria_tabla_registro_created'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Verificacion final sugerida.
SHOW INDEX FROM items_biblioteca;
SHOW INDEX FROM solicitudes_prestamo;
SHOW INDEX FROM visitas_items;
SHOW INDEX FROM auditoria;