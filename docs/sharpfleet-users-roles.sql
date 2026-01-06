-- SharpFleet roles/permissions (phpMyAdmin)
-- PURPOSE: Expand and normalize users.role to support:
--   company_admin, branch_admin, booking_admin, driver
-- Backwards compatibility: legacy 'admin' is mapped to 'company_admin'.
--
-- NOTE: Run these statements against the SHARPFLEET database/connection.
--
-- READ-ONLY CHECKS
SHOW COLUMNS FROM `users` LIKE 'role';
SELECT role, COUNT(*) AS cnt
FROM `users`
GROUP BY role
ORDER BY cnt DESC;

-- =========================
-- DESTRUCTIVE / SCHEMA CHANGE
-- =========================
-- If role is an ENUM limited to ('admin','driver'), this converts it to a VARCHAR.
-- If role is already VARCHAR, this ensures it can store the new role values.
ALTER TABLE `users`
  MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'driver';

-- =========================
-- DATA NORMALIZATION
-- =========================
-- Backfill missing/blank roles.
UPDATE `users`
SET `role` = 'driver'
WHERE `role` IS NULL OR TRIM(`role`) = '';

-- Normalize legacy admin to the new canonical role.
UPDATE `users`
SET `role` = 'company_admin'
WHERE `role` = 'admin';

-- Optional index (safe to keep for performance).
-- If your MySQL version errors on duplicate index names, skip this.
CREATE INDEX `users_role_idx` ON `users`(`role`);
