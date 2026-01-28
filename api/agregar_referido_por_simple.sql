-- Versión simple: Ejecuta directamente en tu base de datos
-- Si la columna ya existe, simplemente ignorará el error

ALTER TABLE users 
ADD COLUMN referido_por VARCHAR(255) NULL 
AFTER estado;
