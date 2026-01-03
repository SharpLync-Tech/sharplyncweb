-- SharpFleet Branches: ROLLBACK (phpMyAdmin)
-- Date: 2026-01-03
--
-- WARNING:
-- - This removes branch assignment columns and drops branch tables.
-- - You will LOSE branch data unless you back it up first.
--
-- Recommended: rename tables first instead of dropping, so rollback is reversible.

-- ---------------------------------------------------------------------
-- Option A (recommended): rename tables (keeps data)
-- ---------------------------------------------------------------------
-- RENAME TABLE branches TO branches_rollback_20260103;
-- RENAME TABLE user_branch_access TO user_branch_access_rollback_20260103;
--
-- Then remove columns:
-- ALTER TABLE vehicles DROP COLUMN branch_id;
-- ALTER TABLE bookings DROP COLUMN branch_id;
-- ALTER TABLE bookings DROP COLUMN timezone;
-- ALTER TABLE trips DROP COLUMN branch_id;
-- ALTER TABLE trips DROP COLUMN timezone;

-- ---------------------------------------------------------------------
-- Option B: hard drop (destructive)
-- ---------------------------------------------------------------------
-- Remove columns (ignore errors if a column doesn't exist)
ALTER TABLE vehicles DROP COLUMN branch_id;
ALTER TABLE bookings DROP COLUMN branch_id;
ALTER TABLE bookings DROP COLUMN timezone;
ALTER TABLE trips DROP COLUMN branch_id;
ALTER TABLE trips DROP COLUMN timezone;

-- Drop tables
DROP TABLE IF EXISTS user_branch_access;
DROP TABLE IF EXISTS branches;
