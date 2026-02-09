-- ============================================================
-- Solo tabla manual_gains (ganancias manuales por mes/año)
-- Usar cuando la base ya existe y solo falta esta tabla.
-- Migraciones: 2026-02-09-000002, 2026-02-09-000003
-- ============================================================

CREATE TABLE IF NOT EXISTS `manual_gains` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gain_year` smallint(4) unsigned NOT NULL,
  `gain_month` tinyint(2) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manual_gains_year_month` (`gain_year`, `gain_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
