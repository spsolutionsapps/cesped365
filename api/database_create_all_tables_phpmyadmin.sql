-- ============================================================
-- Script para phpMyAdmin: crear TODAS las tablas de Cesped365
-- Generado a partir de las migraciones del proyecto.
-- ============================================================
-- Cómo usar:
-- 1. En phpMyAdmin, selecciona tu base de datos (ej. cesped365).
-- 2. Ve a la pestaña "SQL".
-- 3. Pega y ejecuta todo el script.
-- Si alguna tabla ya existe, verás error; en ese caso puedes
-- comentar o borrar el CREATE de esa tabla y ejecutar el resto.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. users (incluye plan, estado, referido_por, lat, lng)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `report_images`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `scheduled_visits`;
DROP TABLE IF EXISTS `user_subscriptions`;
DROP TABLE IF EXISTS `gardens`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `blocked_days`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cliente') NOT NULL DEFAULT 'cliente',
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `plan` varchar(50) DEFAULT 'Urbano',
  `estado` varchar(20) DEFAULT 'Pendiente',
  `referido_por` varchar(255) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL COMMENT 'Latitud GPS (opcional)',
  `lng` decimal(11,8) DEFAULT NULL COMMENT 'Longitud GPS (opcional)',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. gardens
-- ------------------------------------------------------------
CREATE TABLE `gardens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `address` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gardens_user_id_foreign` (`user_id`),
  CONSTRAINT `gardens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. reports (estructura completa: migraciones + columnas usadas por la API)
-- ------------------------------------------------------------
CREATE TABLE `reports` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `garden_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `estado_general` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `cesped_parejo` tinyint(1) NOT NULL DEFAULT 0,
  `color_ok` tinyint(1) NOT NULL DEFAULT 0,
  `manchas` tinyint(1) NOT NULL DEFAULT 0,
  `zonas_desgastadas` tinyint(1) NOT NULL DEFAULT 0,
  `malezas_visibles` tinyint(1) NOT NULL DEFAULT 0,
  `crecimiento_cm` decimal(5,2) DEFAULT NULL,
  `compactacion` varchar(50) DEFAULT NULL,
  `humedad` varchar(50) DEFAULT NULL,
  `plagas` tinyint(1) NOT NULL DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `jardinero` varchar(100) DEFAULT NULL,
  `grass_health` varchar(50) DEFAULT NULL,
  `grass_height_cm` decimal(5,2) DEFAULT NULL,
  `grass_color` varchar(20) DEFAULT NULL,
  `grass_even` tinyint(1) DEFAULT NULL,
  `spots` tinyint(1) DEFAULT NULL,
  `weeds_visible` tinyint(1) DEFAULT NULL,
  `watering_status` varchar(50) DEFAULT NULL,
  `pest_detected` tinyint(1) DEFAULT NULL,
  `pest_description` text DEFAULT NULL,
  `work_done` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `next_visit` date DEFAULT NULL,
  `growth_cm` decimal(5,2) DEFAULT NULL,
  `fertilizer_applied` tinyint(1) DEFAULT NULL,
  `fertilizer_type` varchar(100) DEFAULT NULL,
  `weather_conditions` varchar(255) DEFAULT NULL,
  `technician_notes` text DEFAULT NULL,
  `client_rating` tinyint(1) DEFAULT NULL,
  `client_feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_garden_id_foreign` (`garden_id`),
  KEY `reports_user_id` (`user_id`),
  KEY `reports_visit_date` (`visit_date`),
  CONSTRAINT `reports_garden_id_foreign` FOREIGN KEY (`garden_id`) REFERENCES `gardens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. report_images
-- ------------------------------------------------------------
CREATE TABLE `report_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(11) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_images_report_id_foreign` (`report_id`),
  CONSTRAINT `report_images_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. subscriptions
-- ------------------------------------------------------------
CREATE TABLE `subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `frequency` enum('mensual','trimestral','semestral','anual') NOT NULL DEFAULT 'mensual',
  `visits_per_month` int(11) NOT NULL DEFAULT 4 COMMENT 'Número de visitas por mes',
  `features` text DEFAULT NULL COMMENT 'JSON con características del plan',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. user_subscriptions
-- ------------------------------------------------------------
CREATE TABLE `user_subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `subscription_id` int(11) unsigned NOT NULL,
  `status` enum('activa','pausada','vencida','cancelada') NOT NULL DEFAULT 'activa',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 1,
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'mercadopago, transferencia, etc',
  `external_payment_id` varchar(255) DEFAULT NULL COMMENT 'ID de MercadoPago u otro procesador',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_subscriptions_user_id_foreign` (`user_id`),
  KEY `user_subscriptions_subscription_id_foreign` (`subscription_id`),
  CONSTRAINT `user_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_subscriptions_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. scheduled_visits
-- ------------------------------------------------------------
CREATE TABLE `scheduled_visits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `garden_id` int(11) unsigned NOT NULL,
  `scheduled_date` datetime NOT NULL,
  `scheduled_time` varchar(10) DEFAULT NULL COMMENT 'Hora programada (ej: 09:00, 14:30)',
  `gardener_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('programada','completada','cancelada') NOT NULL DEFAULT 'programada',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scheduled_visits_garden_id_foreign` (`garden_id`),
  KEY `scheduled_date` (`scheduled_date`),
  KEY `status` (`status`),
  CONSTRAINT `scheduled_visits_garden_id_foreign` FOREIGN KEY (`garden_id`) REFERENCES `gardens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. blocked_days
-- ------------------------------------------------------------
CREATE TABLE `blocked_days` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blocked_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blocked_date` (`blocked_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Listo. Tablas creadas: users, gardens, reports, report_images,
-- subscriptions, user_subscriptions, scheduled_visits, blocked_days.
-- ============================================================
