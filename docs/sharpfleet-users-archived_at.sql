-- READ-ONLY (safe): confirm whether the column already exists
SHOW COLUMNS FROM `users` LIKE 'archived_at';

-- DESTRUCTIVE (schema change only): add archived_at (do NOT run if it already exists)
ALTER TABLE `users`
  ADD COLUMN `archived_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- DESTRUCTIVE (schema change only): add index (optional but recommended)
CREATE INDEX `users_archived_at_idx` ON `users` (`archived_at`);
