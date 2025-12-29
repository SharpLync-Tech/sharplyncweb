-- SharpFleet unified audit log table
-- Manage this schema via phpMyAdmin (no Laravel migrations).
-- Connection: sharpfleet

CREATE TABLE IF NOT EXISTS `sharpfleet_audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organisation_id` BIGINT UNSIGNED NOT NULL,

  `actor_type` VARCHAR(30) NOT NULL,
  `actor_id` BIGINT UNSIGNED NULL,
  `actor_email` VARCHAR(190) NULL,
  `actor_name` VARCHAR(190) NULL,

  `action` VARCHAR(190) NOT NULL,

  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(500) NULL,
  `method` VARCHAR(10) NULL,
  `path` VARCHAR(500) NULL,
  `status_code` INT NULL,

  `context_json` LONGTEXT NULL,

  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_sfal_org_created` (`organisation_id`, `created_at`),
  KEY `idx_sfal_actor_type` (`actor_type`),
  KEY `idx_sfal_actor_email` (`actor_email`),
  KEY `idx_sfal_action` (`action`),
  KEY `idx_sfal_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
