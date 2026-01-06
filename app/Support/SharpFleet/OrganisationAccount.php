<?php

namespace App\Support\SharpFleet;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class OrganisationAccount
{
    public const TYPE_PERSONAL = 'personal';
    public const TYPE_SOLE_TRADER = 'sole_trader';
    public const TYPE_COMPANY = 'company';

    /** @var array<int, string> */
    private static array $cache = [];

    public static function forOrganisationId(int $organisationId): string
    {
        if ($organisationId <= 0) {
            return self::TYPE_COMPANY;
        }

        if (isset(self::$cache[$organisationId])) {
            return self::$cache[$organisationId];
        }

        $select = [];
        if (Schema::connection('sharpfleet')->hasColumn('organisations', 'account_type')) {
            $select[] = 'account_type';
        }
        if (Schema::connection('sharpfleet')->hasColumn('organisations', 'company_type')) {
            $select[] = 'company_type';
        }

        if (count($select) === 0) {
            return self::$cache[$organisationId] = self::TYPE_COMPANY;
        }

        $row = DB::connection('sharpfleet')
            ->table('organisations')
            ->select($select)
            ->where('id', $organisationId)
            ->first();

        $accountType = $row && isset($row->account_type) ? strtolower(trim((string) $row->account_type)) : '';
        if (in_array($accountType, [self::TYPE_PERSONAL, self::TYPE_SOLE_TRADER, self::TYPE_COMPANY], true)) {
            return self::$cache[$organisationId] = $accountType;
        }

        $companyType = $row && isset($row->company_type) ? strtolower(trim((string) $row->company_type)) : '';
        if ($companyType === self::TYPE_SOLE_TRADER) {
            return self::$cache[$organisationId] = self::TYPE_SOLE_TRADER;
        }

        if ($companyType === self::TYPE_COMPANY) {
            return self::$cache[$organisationId] = self::TYPE_COMPANY;
        }

        // Backwards compatible default.
        return self::$cache[$organisationId] = self::TYPE_COMPANY;
    }

    public static function bookingsEnabled(int $organisationId): bool
    {
        return self::forOrganisationId($organisationId) === self::TYPE_COMPANY;
    }

    public static function usersEnabled(int $organisationId): bool
    {
        return self::forOrganisationId($organisationId) === self::TYPE_COMPANY;
    }

    public static function customersEnabled(int $organisationId): bool
    {
        return in_array(self::forOrganisationId($organisationId), [self::TYPE_COMPANY, self::TYPE_SOLE_TRADER], true);
    }

    public static function wizardIncludesCustomerStep(int $organisationId): bool
    {
        return self::customersEnabled($organisationId);
    }
}
