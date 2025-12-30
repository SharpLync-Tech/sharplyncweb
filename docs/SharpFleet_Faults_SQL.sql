-- SharpFleet (tenant DB) - Fault / Incident Reporting
-- Run in phpMyAdmin against the SharpFleet tenant database (the DB used by DB::connection('sharpfleet')).
--
-- This script is safe to run once. If the table already exists, review before running.

CREATE TABLE `faults` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organisation_id` INT NOT NULL,
  `vehicle_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `trip_id` BIGINT UNSIGNED NULL,

  `severity` ENUM('minor','major','critical') NOT NULL DEFAULT 'minor',
  `title` VARCHAR(150) NULL,
  `description` TEXT NOT NULL,
  `occurred_at` DATETIME NULL,

  `status` ENUM('open','in_review','resolved','dismissed') NOT NULL DEFAULT 'open',

  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_faults_org_created` (`organisation_id`, `created_at`),
  KEY `idx_faults_org_status` (`organisation_id`, `status`),
  KEY `idx_faults_vehicle` (`vehicle_id`),
  KEY `idx_faults_trip` (`trip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
