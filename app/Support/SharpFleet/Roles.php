<?php

namespace App\Support\SharpFleet;

final class Roles
{
    public const COMPANY_ADMIN = 'company_admin';
    public const BRANCH_ADMIN = 'branch_admin';
    public const BOOKING_ADMIN = 'booking_admin';
    public const DRIVER = 'driver';

    /**
     * Normalize database/session role values to the canonical set.
     * - Legacy 'admin' is treated as 'company_admin'.
     */
    public static function normalize(?string $role): string
    {
        $role = strtolower(trim((string) $role));

        if ($role === 'admin') {
            return self::COMPANY_ADMIN;
        }

        if (in_array($role, [self::COMPANY_ADMIN, self::BRANCH_ADMIN, self::BOOKING_ADMIN, self::DRIVER], true)) {
            return $role;
        }

        // Default to the lowest-privilege role.
        return self::DRIVER;
    }

    /**
     * True if this user can enter the admin portal at all.
     */
    public static function isAdminPortal(array $user): bool
    {
        return in_array(self::normalize($user['role'] ?? null), [
            self::COMPANY_ADMIN,
            self::BRANCH_ADMIN,
            self::BOOKING_ADMIN,
        ], true);
    }

    public static function isCompanyAdmin(array $user): bool
    {
        return self::normalize($user['role'] ?? null) === self::COMPANY_ADMIN;
    }

    public static function canManageBookings(array $user): bool
    {
        return self::isAdminPortal($user);
    }

    /**
     * Reporting screens (Trip reports).
     * Intentionally excludes booking_admin.
     */
    public static function canViewReports(array $user): bool
    {
        return in_array(self::normalize($user['role'] ?? null), [self::COMPANY_ADMIN, self::BRANCH_ADMIN], true);
    }

    /**
     * Faults/incident reporting admin screens.
     * Intentionally excludes booking_admin.
     */
    public static function canManageFaults(array $user): bool
    {
        return in_array(self::normalize($user['role'] ?? null), [self::COMPANY_ADMIN, self::BRANCH_ADMIN], true);
    }

    /**
     * Reminders admin screen.
     * Intentionally excludes booking_admin.
     */
    public static function canViewReminders(array $user): bool
    {
        return in_array(self::normalize($user['role'] ?? null), [self::COMPANY_ADMIN, self::BRANCH_ADMIN], true);
    }

    /**
     * Safety checklist (company-level checklist used in driver flow).
     * Branch admins may view, but only company admins may update.
     */
    public static function canViewSafetyChecks(array $user): bool
    {
        return in_array(self::normalize($user['role'] ?? null), [self::COMPANY_ADMIN, self::BRANCH_ADMIN], true);
    }

    public static function canUpdateSafetyChecks(array $user): bool
    {
        return self::isCompanyAdmin($user);
    }

    public static function canManageFleet(array $user): bool
    {
        return in_array(self::normalize($user['role'] ?? null), [self::COMPANY_ADMIN, self::BRANCH_ADMIN], true);
    }

    public static function canManageUsers(array $user): bool
    {
        return in_array(self::normalize($user['role'] ?? null), [self::COMPANY_ADMIN, self::BRANCH_ADMIN], true);
    }

    /**
     * Users/Drivers screen access for assigning user groups.
     * Note: does NOT imply permission to archive/unarchive users.
     */
    public static function canSetUserGroups(array $user): bool
    {
        return in_array(self::normalize($user['role'] ?? null), [self::COMPANY_ADMIN, self::BRANCH_ADMIN], true);
    }

    public static function canManageBranches(array $user): bool
    {
        return self::isCompanyAdmin($user);
    }

    public static function canManageCompanySettings(array $user): bool
    {
        return self::isCompanyAdmin($user);
    }

    /**
     * Company admins bypass branch access restrictions; everyone else does not.
     */
    public static function bypassesBranchRestrictions(array $user): bool
    {
        return self::isCompanyAdmin($user);
    }

    /**
     * Driver UI access is role=driver OR an admin-type user with is_driver enabled.
     */
    public static function isDriver(array $user): bool
    {
        $role = self::normalize($user['role'] ?? null);
        $isDriverFlag = !empty($user['is_driver']);

        if ($role === self::DRIVER) {
            return $isDriverFlag || !array_key_exists('is_driver', $user);
        }

        return $isDriverFlag;
    }
}
