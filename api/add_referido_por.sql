-- Script para agregar el campo referido_por a la tabla users
-- Ejecutar este script en tu base de datos MySQL si la migración no funciona

-- Verificar si la columna existe antes de agregarla
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'referido_por';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER estado')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
