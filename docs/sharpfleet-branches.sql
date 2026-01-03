-- SharpFleet Branches: INSTALL (phpMyAdmin)
-- Date: 2026-01-03
--
-- Notes:
-- - This script is written for MySQL/MariaDB.
-- - It is designed to be safe to run on existing installs (IF NOT EXISTS / schema checks where possible).
-- - If your MySQL version does not support JSON_EXTRACT, see the "No JSON" fallback section.

-- ---------------------------------------------------------------------
-- 0) BACKUP RECOMMENDED (manual)
-- ---------------------------------------------------------------------
-- CREATE TABLE branches_backup_20260103 AS SELECT * FROM branches;
-- CREATE TABLE user_branch_access_backup_20260103 AS SELECT * FROM user_branch_access;
-- (You can also export the DB in phpMyAdmin.)

-- ---------------------------------------------------------------------
-- 1) Create branches table
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS branches (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  organisation_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  timezone VARCHAR(100) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_branches_org (organisation_id),
  KEY idx_branches_org_active (organisation_id, is_active),
  KEY idx_branches_org_default (organisation_id, is_default),
  UNIQUE KEY uniq_branches_org_name (organisation_id, name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2) Create user-branch access table
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_branch_access (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  organisation_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_uba_org_user_branch (organisation_id, user_id, branch_id),
  KEY idx_uba_org_user (organisation_id, user_id),
  KEY idx_uba_org_branch (organisation_id, branch_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3) Add branch_id to vehicles
-- ---------------------------------------------------------------------
-- NOTE: MySQL doesn't support IF NOT EXISTS for ADD COLUMN on older versions.
-- If you get an error "Duplicate column name", ignore it.
ALTER TABLE vehicles ADD COLUMN branch_id INT UNSIGNED NULL;
ALTER TABLE vehicles ADD KEY idx_vehicles_org_branch (organisation_id, branch_id);

-- ---------------------------------------------------------------------
-- 4) Add branch/timezone columns to bookings
-- ---------------------------------------------------------------------
ALTER TABLE bookings ADD COLUMN branch_id INT UNSIGNED NULL;
ALTER TABLE bookings ADD COLUMN timezone VARCHAR(100) NULL;
ALTER TABLE bookings ADD KEY idx_bookings_org_branch (organisation_id, branch_id);

-- ---------------------------------------------------------------------
-- 5) Add branch/timezone columns to trips
-- ---------------------------------------------------------------------
ALTER TABLE trips ADD COLUMN branch_id INT UNSIGNED NULL;
ALTER TABLE trips ADD COLUMN timezone VARCHAR(100) NULL;
ALTER TABLE trips ADD KEY idx_trips_org_branch (organisation_id, branch_id);

-- ---------------------------------------------------------------------
-- 6) Backfill: create default "Main Branch" per organisation
-- ---------------------------------------------------------------------
-- Preferred: pull timezone from company_settings.settings_json->timezone.
INSERT INTO branches (organisation_id, name, timezone, is_default, is_active, created_at, updated_at)
SELECT cs.organisation_id,
       'Main Branch',
       COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(cs.settings_json, '$.timezone')), ''), 'Australia/Brisbane'),
       1,
       1,
       NOW(),
       NOW()
FROM company_settings cs
LEFT JOIN branches b
  ON b.organisation_id = cs.organisation_id
 AND b.is_default = 1
WHERE b.id IS NULL;

-- Fallback: organisations without company_settings row
INSERT INTO branches (organisation_id, name, timezone, is_default, is_active, created_at, updated_at)
SELECT o.id,
       'Main Branch',
       'Australia/Brisbane',
       1,
       1,
       NOW(),
       NOW()
FROM organisations o
LEFT JOIN branches b
  ON b.organisation_id = o.id
 AND b.is_default = 1
WHERE b.id IS NULL;

-- ---------------------------------------------------------------------
-- 7) Backfill: assign existing vehicles/bookings/trips to default branch
-- ---------------------------------------------------------------------
UPDATE vehicles v
JOIN branches b
  ON b.organisation_id = v.organisation_id
 AND b.is_default = 1
SET v.branch_id = b.id
WHERE v.branch_id IS NULL;

UPDATE bookings bk
JOIN vehicles v
  ON v.organisation_id = bk.organisation_id
 AND v.id = bk.vehicle_id
SET bk.branch_id = v.branch_id
WHERE bk.branch_id IS NULL;

UPDATE trips t
JOIN vehicles v
  ON v.organisation_id = t.organisation_id
 AND v.id = t.vehicle_id
SET t.branch_id = v.branch_id
WHERE t.branch_id IS NULL;

-- ---------------------------------------------------------------------
-- 8) Backfill: user branch access
-- ---------------------------------------------------------------------
-- Everyone gets default branch access.
INSERT IGNORE INTO user_branch_access (organisation_id, user_id, branch_id, is_active, created_at, updated_at)
SELECT u.organisation_id, u.id, b.id, 1, NOW(), NOW()
FROM users u
JOIN branches b
  ON b.organisation_id = u.organisation_id
 AND b.is_default = 1;

-- Admins get access to all active branches.
INSERT IGNORE INTO user_branch_access (organisation_id, user_id, branch_id, is_active, created_at, updated_at)
SELECT u.organisation_id, u.id, b.id, 1, NOW(), NOW()
FROM users u
JOIN branches b
  ON b.organisation_id = u.organisation_id
WHERE u.role = 'admin'
  AND (b.is_active = 1 OR b.is_active IS NULL);

-- ---------------------------------------------------------------------
-- No JSON fallback (if JSON_EXTRACT is unsupported)
-- ---------------------------------------------------------------------
-- If the INSERT INTO branches ... JSON_EXTRACT(...) fails, delete those failed queries
-- and run this instead:
--
-- INSERT INTO branches (organisation_id, name, timezone, is_default, is_active, created_at, updated_at)
-- SELECT organisation_id, 'Main Branch', 'Australia/Brisbane', 1, 1, NOW(), NOW()
-- FROM company_settings cs
-- LEFT JOIN branches b ON b.organisation_id = cs.organisation_id AND b.is_default = 1
-- WHERE b.id IS NULL;
