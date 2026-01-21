/* ===================================================== */
/* CESPED365 - INSTALACIÓN PASO A PASO                   */
/* ===================================================== */
/* Si el script completo falla, ejecuta estas queries   */
/* UNA POR UNA en phpMyAdmin                             */
/* ===================================================== */

/* ==================== PASO 1 ========================= */
/* Eliminar tablas existentes (si las hay)              */

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `report_images`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `gardens`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS=1;

/* ==================== PASO 2 ========================= */
/* Crear tabla USERS                                     */

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cliente') DEFAULT 'cliente',
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `plan` varchar(50) DEFAULT 'Urbano',
  `estado` varchar(20) DEFAULT 'Pendiente',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* ==================== PASO 3 ========================= */
/* Crear tabla GARDENS                                   */

CREATE TABLE `gardens` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `address` varchar(255) NOT NULL,
  `size_m2` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `gardens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* ==================== PASO 4 ========================= */
/* Crear tabla REPORTS                                   */

CREATE TABLE `reports` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `garden_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `visit_date` date NOT NULL,
  `status` enum('completado','pendiente','cancelado') DEFAULT 'pendiente',
  `grass_height_cm` decimal(5,2) DEFAULT NULL,
  `grass_health` enum('excelente','bueno','regular','malo') DEFAULT NULL,
  `watering_status` enum('optimo','requiere_ajuste','insuficiente') DEFAULT NULL,
  `pest_detected` tinyint(1) DEFAULT 0,
  `pest_description` text DEFAULT NULL,
  `work_done` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `next_visit` date DEFAULT NULL,
  `growth_cm` decimal(5,2) DEFAULT NULL,
  `fertilizer_applied` tinyint(1) DEFAULT 0,
  `fertilizer_type` varchar(100) DEFAULT NULL,
  `weather_conditions` varchar(100) DEFAULT NULL,
  `technician_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `garden_id` (`garden_id`),
  KEY `user_id` (`user_id`),
  KEY `visit_date` (`visit_date`),
  KEY `status` (`status`),
  CONSTRAINT `reports_garden_id_foreign` FOREIGN KEY (`garden_id`) REFERENCES `gardens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* ==================== PASO 5 ========================= */
/* Crear tabla REPORT_IMAGES                             */

CREATE TABLE `report_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` enum('before','after','detail','problem') DEFAULT 'detail',
  `description` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `report_images_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* ==================== PASO 6 ========================= */
/* Insertar usuario ADMIN                                */

INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `plan`, `estado`) 
VALUES (
  'Administrador',
  'admin@cesped365.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',
  NULL,
  NULL,
  'Urbano',
  'Activo'
);

/* ===================================================== */
/* COMPLETADO!                                           */
/* ===================================================== */
/* Login: admin@cesped365.com                            */
/* Password: password                                    */
/* ===================================================== */
