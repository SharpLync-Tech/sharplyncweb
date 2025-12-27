<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VehicleReminderService
{
    public function buildDigest(int $organisationId, int $registrationDays = 30, int $serviceDays = 30, int $serviceReadingThreshold = 500): array
    {
        $today = Carbon::today();
        $regCutoff = $today->copy()->addDays($registrationDays);
        $serviceDateCutoff = $today->copy()->addDays($serviceDays);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select(
                'id',
                'name',
                'tracking_mode',
                'is_active',
                'is_road_registered',
                'registration_number',
                'starting_km',
                'registration_expiry',
                'service_due_date',
                'service_due_km'
            )
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->get();

        $latestEndedAt = DB::connection('sharpfleet')
            ->table('trips')
            ->selectRaw('vehicle_id, MAX(ended_at) as max_ended_at')
            ->where('organisation_id', $organisationId)
            ->whereNotNull('end_km')
            ->whereNotNull('ended_at')
            ->groupBy('vehicle_id');

        $lastTrips = DB::connection('sharpfleet')
            ->table('trips as t')
            ->joinSub($latestEndedAt, 'lt', function ($join) {
                $join
                    ->on('t.vehicle_id', '=', 'lt.vehicle_id')
                    ->on('t.ended_at', '=', 'lt.max_ended_at');
            })
            ->where('t.organisation_id', $organisationId)
            ->select('t.vehicle_id', 't.end_km', 't.ended_at')
            ->get()
            ->keyBy('vehicle_id');

        $registration = [
            'overdue' => [],
            'due_soon' => [],
        ];

        $serviceDate = [
            'overdue' => [],
            'due_soon' => [],
        ];

        $serviceReading = [
            'overdue' => [],
            'due_soon' => [],
        ];

        foreach ($vehicles as $v) {
            // Registration expiry reminders
            if ((int) ($v->is_road_registered ?? 0) === 1 && !empty($v->registration_expiry)) {
                $expiry = Carbon::parse((string) $v->registration_expiry)->startOfDay();

                if ($expiry->lessThan($today)) {
                    $registration['overdue'][] = [
                        'vehicle_id' => (int) $v->id,
                        'name' => $v->name,
                        'registration_number' => $v->registration_number,
                        'date' => $expiry,
                        'days' => $expiry->diffInDays($today),
                    ];
                } elseif ($expiry->lessThanOrEqualTo($regCutoff)) {
                    $registration['due_soon'][] = [
                        'vehicle_id' => (int) $v->id,
                        'name' => $v->name,
                        'registration_number' => $v->registration_number,
                        'date' => $expiry,
                        'days' => $today->diffInDays($expiry),
                    ];
                }
            }

            // Service due by date reminders
            if (!empty($v->service_due_date)) {
                $due = Carbon::parse((string) $v->service_due_date)->startOfDay();

                if ($due->lessThan($today)) {
                    $serviceDate['overdue'][] = [
                        'vehicle_id' => (int) $v->id,
                        'name' => $v->name,
                        'date' => $due,
                        'days' => $due->diffInDays($today),
                    ];
                } elseif ($due->lessThanOrEqualTo($serviceDateCutoff)) {
                    $serviceDate['due_soon'][] = [
                        'vehicle_id' => (int) $v->id,
                        'name' => $v->name,
                        'date' => $due,
                        'days' => $today->diffInDays($due),
                    ];
                }
            }

            // Service due by reading reminders (km/hours reading stored in trips.end_km)
            if (!empty($v->service_due_km)) {
                $last = $lastTrips[$v->id] ?? null;
                $lastReading = null;

                if ($last && $last->end_km !== null) {
                    $lastReading = (int) $last->end_km;
                } elseif ($v->starting_km !== null) {
                    $lastReading = (int) $v->starting_km;
                }

                if ($lastReading !== null) {
                    $dueReading = (int) $v->service_due_km;
                    $delta = $dueReading - $lastReading;

                    if ($delta <= 0) {
                        $serviceReading['overdue'][] = [
                            'vehicle_id' => (int) $v->id,
                            'name' => $v->name,
                            'tracking_mode' => $v->tracking_mode ?? 'distance',
                            'last_reading' => $lastReading,
                            'due_reading' => $dueReading,
                            'delta' => $delta,
                        ];
                    } elseif ($delta <= $serviceReadingThreshold) {
                        $serviceReading['due_soon'][] = [
                            'vehicle_id' => (int) $v->id,
                            'name' => $v->name,
                            'tracking_mode' => $v->tracking_mode ?? 'distance',
                            'last_reading' => $lastReading,
                            'due_reading' => $dueReading,
                            'delta' => $delta,
                        ];
                    }
                }
            }
        }

        return [
            'organisation' => $organisation,
            'registration' => $registration,
            'serviceDate' => $serviceDate,
            'serviceReading' => $serviceReading,
            'settings' => [
                'registration_days' => $registrationDays,
                'service_days' => $serviceDays,
                'service_reading_threshold' => $serviceReadingThreshold,
            ],
        ];
    }
}
