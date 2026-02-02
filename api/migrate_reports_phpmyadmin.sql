-- ============================================================
-- Script para phpMyAdmin: nuevas columnas en tabla reports
-- (Equivalente a las migraciones 2026-02-02-000001 y 000002)
-- ============================================================
-- Cómo usar:
-- 1. En phpMyAdmin, selecciona tu base de datos (ej. cesped365).
-- 2. Ve a la pestaña "SQL".
-- 3. Pega y ejecuta cada bloque por separado (o todo si tu MySQL lo permite).
-- 4. Si un ALTER falla con "Duplicate column name", esa columna ya existe:
--    ignora el error y sigue con el siguiente.
-- ============================================================

-- 1) grass_color (después de grass_health)
ALTER TABLE `reports`
  ADD COLUMN `grass_color` VARCHAR(20) NULL DEFAULT NULL AFTER `grass_health`;

-- 2) grass_even (después de grass_color)
ALTER TABLE `reports`
  ADD COLUMN `grass_even` TINYINT(1) NULL DEFAULT NULL AFTER `grass_color`;

-- 3) spots (después de grass_even)
ALTER TABLE `reports`
  ADD COLUMN `spots` TINYINT(1) NULL DEFAULT NULL AFTER `grass_even`;

-- 4) weeds_visible (después de spots)
ALTER TABLE `reports`
  ADD COLUMN `weeds_visible` TINYINT(1) NULL DEFAULT NULL AFTER `spots`;
