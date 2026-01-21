-- =====================================================
-- CESPED365 - Base de Datos de Producción
-- =====================================================
-- Ejecutar este script completo en phpMyAdmin
-- después de crear la base de datos
-- =====================================================

SET FOREIGN_KEY_CHECKS=0;

-- =====================================================
-- 1. TABLA: users
-- =====================================================
DROP TABLE IF EXISTS `users`;

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

-- =====================================================
-- 2. TABLA: gardens
-- =====================================================
DROP TABLE IF EXISTS `gardens`;

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

-- =====================================================
-- 3. TABLA: reports
-- =====================================================
DROP TABLE IF EXISTS `reports`;

CREATE TABLE `reports` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `garden_id` int(11) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `estado_general` enum('excelente','bueno','regular','malo') DEFAULT 'bueno',
  `altura_cm` decimal(5,2) DEFAULT NULL,
  `crecimiento_cm` decimal(5,2) DEFAULT NULL,
  `color` enum('verde_intenso','verde','amarillento','marron') DEFAULT 'verde',
  `densidad` enum('muy_densa','densa','media','rala') DEFAULT 'densa',
  `malezas_visibles` tinyint(1) DEFAULT 0,
  `manchas` tinyint(1) DEFAULT 0,
  `zonas_desgastadas` tinyint(1) DEFAULT 0,
  `riego` enum('optimo','excesivo','insuficiente') DEFAULT 'optimo',
  `observaciones` text DEFAULT NULL,
  `jardinero` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `garden_id` (`garden_id`),
  CONSTRAINT `reports_garden_id_foreign` FOREIGN KEY (`garden_id`) REFERENCES `gardens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABLA: report_images
-- =====================================================
DROP TABLE IF EXISTS `report_images`;

CREATE TABLE `report_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `report_images_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

-- =====================================================
-- 5. DATOS INICIALES: Usuario Admin
-- =====================================================

-- Usuario admin
-- Email: admin@cesped365.com
-- Password: admin123 (¡CAMBIAR después del primer login!)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `plan`, `estado`)
VALUES (
  'Administrador',
  'admin@cesped365.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',
  '+54 11 1234-5678',
  'Oficina Central',
  'Urbano',
  'Activo'
);

-- =====================================================
-- 6. DATOS DE PRUEBA (Opcional - comentar si no deseas)
-- =====================================================

-- Cliente de prueba 1
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `plan`, `estado`)
VALUES (
  'Juan Pérez',
  'cliente@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: cliente123
  'cliente',
  '+54 11 9876-5432',
  'Av. Corrientes 1234, CABA',
  'Residencial',
  'Activo'
);

-- Jardín del cliente de prueba
INSERT INTO `gardens` (`user_id`, `address`, `size_m2`, `notes`)
SELECT id, 'Av. Corrientes 1234, CABA', 150.00, 'Jardín residencial con riego automático'
FROM `users` WHERE email = 'cliente@example.com';

-- Reporte de prueba
INSERT INTO `reports` (
  `garden_id`,
  `date`,
  `estado_general`,
  `altura_cm`,
  `crecimiento_cm`,
  `color`,
  `densidad`,
  `malezas_visibles`,
  `manchas`,
  `zonas_desgastadas`,
  `riego`,
  `observaciones`,
  `jardinero`,
  `direccion`
)
SELECT 
  g.id,
  CURDATE(),
  'bueno',
  5.50,
  1.20,
  'verde',
  'densa',
  0,
  0,
  0,
  'optimo',
  'Césped en excelente estado, mantenimiento regular aplicado.',
  'Carlos Rodríguez',
  g.address
FROM `gardens` g
INNER JOIN `users` u ON g.user_id = u.id
WHERE u.email = 'cliente@example.com';

-- =====================================================
-- ✅ SCRIPT COMPLETADO
-- =====================================================
-- 
-- Próximos pasos:
-- 1. Verificar que todas las tablas se crearon correctamente
-- 2. Hacer login con admin@cesped365.com / admin123
-- 3. ¡CAMBIAR la contraseña del admin inmediatamente!
-- 
-- =====================================================
