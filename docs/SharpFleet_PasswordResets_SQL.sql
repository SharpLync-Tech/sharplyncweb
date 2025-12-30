-- SharpFleet Password Reset (phpMyAdmin)
-- Purpose: Create supporting table for SharpFleet password reset tokens.
-- Notes:
-- - Run this against the SharpFleet database (the one used by DB::connection('sharpfleet')).
-- - This does NOT modify existing tables.
-- - Tokens are stored as SHA-256 hashes (token_hash).

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_password_resets_email` (`email`),
  INDEX `idx_password_resets_token_hash` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
