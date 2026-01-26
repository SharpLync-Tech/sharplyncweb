<?php

namespace App\Services\SharpFleet;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerService
{
    public function customersTableExists(): bool
    {
        return Schema::connection('sharpfleet')->hasTable('customers');
    }

    /**
     * List active customers for an organisation.
     */
    public function getCustomers(int $organisationId, int $limit = 500)
    {
        if (!$this->customersTableExists()) {
            return collect();
        }

        return DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * Get a single customer for an organisation.
     */
    public function getCustomer(int $organisationId, int $customerId)
    {
        if (!$this->customersTableExists()) {
            return null;
        }

        $query = DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $organisationId)
            ->where('id', $customerId);

        if (Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id')) {
            $query->select('id', 'name', 'is_active', 'branch_id');
        } else {
            $query->select('id', 'name', 'is_active');
        }

        return $query->first();
    }

    /**
     * Update customer name (scoped to organisation).
     */
    public function updateCustomer(int $organisationId, int $customerId, string $name, ?int $branchId = null): void
    {
        if (!$this->customersTableExists()) {
            return;
        }

        $payload = [
            'name' => trim($name),
            'updated_at' => now(),
        ];

        if (Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id')) {
            $payload['branch_id'] = $branchId ?: null;
        }

        DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $organisationId)
            ->where('id', $customerId)
            ->update($payload);
    }

    /**
     * Archive customer (soft archive via is_active=0).
     */
    public function archiveCustomer(int $organisationId, int $customerId): void
    {
        if (!$this->customersTableExists()) {
            return;
        }

        DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $organisationId)
            ->where('id', $customerId)
            ->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
    }

    /**
     * Create a single customer.
     */
    public function createCustomer(int $organisationId, string $name, ?int $branchId = null): int
    {
        $name = trim($name);

        // If it already exists (even inactive), reuse it.
        $existing = DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $organisationId)
            ->where('name', $name)
            ->select('id', 'is_active')
            ->first();

        if ($existing) {
            if ((int) $existing->is_active !== 1) {
                DB::connection('sharpfleet')
                    ->table('customers')
                    ->where('organisation_id', $organisationId)
                    ->where('id', (int) $existing->id)
                    ->update([
                        'is_active'  => 1,
                        'updated_at' => now(),
                    ]);
            }

            return (int) $existing->id;
        }

        $payload = [
            'organisation_id' => $organisationId,
            'name'            => $name,
            'is_active'       => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];

        if (Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id')) {
            $payload['branch_id'] = $branchId ?: null;
        }

        return (int) DB::connection('sharpfleet')
            ->table('customers')
            ->insertGetId($payload);
    }

    /**
     * Resolve a customer name by id (scoped to organisation).
     */
    public function getCustomerNameById(int $organisationId, int $customerId): ?string
    {
        if (!$this->customersTableExists()) {
            return null;
        }

        $row = DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $organisationId)
            ->where('id', $customerId)
            ->where('is_active', 1)
            ->select('name')
            ->first();

        return $row?->name ? trim((string) $row->name) : null;
    }

    /**
     * Import customers from a CSV file.
     * - Uses the first column as the customer display name.
     * - Skips empty rows.
     * - Dedupes against existing names in this organisation (case-insensitive).
     */
    public function importCustomersFromCsv(int $organisationId, UploadedFile $file, int $maxRows = 5000): array
    {
        if (!$this->customersTableExists()) {
            return ['imported' => 0, 'skipped' => 0];
        }

        $existing = DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $organisationId)
            ->pluck('name')
            ->map(fn ($n) => mb_strtolower(trim((string) $n)))
            ->filter()
            ->values()
            ->all();

        $existingSet = array_fill_keys($existing, true);

        $imported = 0;
        $skipped  = 0;

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return ['imported' => 0, 'skipped' => 0];
        }

        $rowCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;
            if ($rowCount > $maxRows) {
                break;
            }

            $name = isset($row[0]) ? trim((string) $row[0]) : '';
            if ($name === '') {
                $skipped++;
                continue;
            }

            $key = mb_strtolower($name);
            if (isset($existingSet[$key])) {
                $skipped++;
                continue;
            }

            $this->createCustomer($organisationId, $name);
            $existingSet[$key] = true;
            $imported++;
        }

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }
}
