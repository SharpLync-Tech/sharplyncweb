<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PrivateVehicleSlotService
{
    private const TABLE = 'sharpfleet_private_vehicle_slots';

    public function slotsTableExists(): bool
    {
        return Schema::connection('sharpfleet')->hasTable(self::TABLE);
    }

    public function calculateSlotCount(int $organisationId): int
    {
        $fleetSize = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        return (int) floor($fleetSize * 0.4);
    }

    public function ensureSlotsInitialized(int $organisationId): void
    {
        if (!$this->slotsTableExists()) {
            return;
        }

        $targetSlots = $this->calculateSlotCount($organisationId);
        if ($targetSlots <= 0) {
            return;
        }

        $existing = DB::connection('sharpfleet')
            ->table(self::TABLE)
            ->where('company_id', $organisationId)
            ->count();

        if ($existing >= $targetSlots) {
            return;
        }

        $rows = [];
        for ($i = $existing + 1; $i <= $targetSlots; $i++) {
            $rows[] = [
                'company_id' => $organisationId,
                'slot_index' => $i,
                'is_active' => 0,
            ];
        }

        DB::connection('sharpfleet')->table(self::TABLE)->insert($rows);
    }

    public function acquireSlot(int $organisationId, Carbon $now): int
    {
        if (!$this->slotsTableExists()) {
            throw ValidationException::withMessages([
                'trip_mode' => 'Private vehicle trips are not available yet.',
            ]);
        }

        return (int) DB::connection('sharpfleet')->transaction(function () use ($organisationId, $now) {
            $slot = DB::connection('sharpfleet')
                ->table(self::TABLE)
                ->where('company_id', $organisationId)
                ->where('is_active', 0)
                ->orderBy('slot_index')
                ->lockForUpdate()
                ->first();

            if (!$slot) {
                throw ValidationException::withMessages([
                    'trip_mode' => 'No private vehicle slots are available right now.',
                ]);
            }

            DB::connection('sharpfleet')
                ->table(self::TABLE)
                ->where('id', $slot->id)
                ->update([
                    'is_active' => 1,
                    'activated_at' => $now,
                    'released_at' => null,
                ]);

            return $slot->id;
        });
    }

    public function assignTripToSlot(int $slotId, int $tripId): void
    {
        if (!$this->slotsTableExists()) {
            return;
        }

        DB::connection('sharpfleet')
            ->table(self::TABLE)
            ->where('id', $slotId)
            ->update([
                'active_trip_id' => $tripId,
            ]);
    }

    public function releaseSlot(int $slotId, Carbon $now): void
    {
        if (!$this->slotsTableExists()) {
            return;
        }

        DB::connection('sharpfleet')
            ->table(self::TABLE)
            ->where('id', $slotId)
            ->update([
                'is_active' => 0,
                'active_trip_id' => null,
                'released_at' => $now,
            ]);
    }
}
