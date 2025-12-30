-- SharpFleet (tenant DB) - Vehicle Issue / Accident Reporting
-- Run in phpMyAdmin against the SharpFleet tenant database (the DB used by DB::connection('sharpfleet')).
--
-- IMPORTANT: Choose ONE path:
-- - Existing installs: run ONLY the ALTER statements in the "Existing installs" section.
-- - New installs: run ONLY the CREATE TABLE statement in the "New installs" section.

-- =====================================================
-- Existing installs (table already exists)
-- =====================================================
-- If you already have a `faults` table, run the ALTER statements below (in order):
--
-- 1) Add report type (Vehicle Issue vs Vehicle Accident)
-- 2) Add archived status
--
-- NOTE: If your MySQL version is older and rejects the ENUM modify, you may need to recreate the enum.

-- 1) Add report_type column
ALTER TABLE `faults`
  ADD COLUMN `report_type` ENUM('issue','accident') NOT NULL DEFAULT 'issue' AFTER `trip_id`;

-- 2) Extend status enum to include archived
ALTER TABLE `faults`
  MODIFY `status` ENUM('open','in_review','resolved','dismissed','archived') NOT NULL DEFAULT 'open';

-- =====================================================
-- New installs (create the table)
-- =====================================================

CREATE TABLE `faults` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organisation_id` INT NOT NULL,
  `vehicle_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `trip_id` BIGINT UNSIGNED NULL,

  `report_type` ENUM('issue','accident') NOT NULL DEFAULT 'issue',

  `severity` ENUM('minor','major','critical') NOT NULL DEFAULT 'minor',
  `title` VARCHAR(150) NULL,
  `description` TEXT NOT NULL,
  `occurred_at` DATETIME NULL,

  `status` ENUM('open','in_review','resolved','dismissed','archived') NOT NULL DEFAULT 'open',

  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_faults_org_created` (`organisation_id`, `created_at`),
  KEY `idx_faults_org_status` (`organisation_id`, `status`),
  KEY `idx_faults_vehicle` (`vehicle_id`),
  KEY `idx_faults_trip` (`trip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
