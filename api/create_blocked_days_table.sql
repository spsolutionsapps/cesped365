-- Crear tabla blocked_days (días bloqueados para turnos)
-- Ejecutar en phpMyAdmin o cliente MySQL si la migración no se puede correr.

CREATE TABLE IF NOT EXISTS `blocked_days` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blocked_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blocked_date` (`blocked_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
