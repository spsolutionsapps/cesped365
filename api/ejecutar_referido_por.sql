-- Ejecuta este SQL directamente en tu base de datos MySQL
-- Puedes hacerlo desde phpMyAdmin, MySQL Workbench, o línea de comandos

-- Verificar si la columna existe y agregarla si no existe
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'referido_por';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN referido_por VARCHAR(255) NULL AFTER estado',
    'SELECT "La columna referido_por ya existe" AS mensaje');

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
