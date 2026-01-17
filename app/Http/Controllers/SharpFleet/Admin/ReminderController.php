<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\VehicleReminderService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReminders($user)) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'Missing organisation');
        }

        $branchService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchScopeEnabled = !$bypassBranchRestrictions
            && $branchService->branchesEnabled()
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) ($user['id'] ?? 0))
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $settingsService = new CompanySettingsService($organisationId);

        $timezone = $settingsService->timezone();
        $dateFormat = $settingsService->dateFormat();

        $regoEnabled = $settingsService->vehicleRegistrationTrackingEnabled();
        $serviceEnabled = $settingsService->vehicleServicingTrackingEnabled();

        $registrationDays = $settingsService->reminderRegistrationDays();
        $serviceDays = $settingsService->reminderServiceDays();
        $serviceReadingThreshold = $settingsService->reminderServiceReadingThreshold();

        $recipient = $this->resolveSubscriberAdminEmail($organisationId);

        $digest = [
            'registration' => ['overdue' => [], 'due_soon' => []],
            'serviceDate' => ['overdue' => [], 'due_soon' => []],
            'serviceReading' => ['overdue' => [], 'due_soon' => []],
            'settings' => [
                'registration_days' => $registrationDays,
                'service_days' => $serviceDays,
                'service_reading_threshold' => $serviceReadingThreshold,
            ],
        ];

        if ($regoEnabled || $serviceEnabled) {
            $digest = (new VehicleReminderService())->buildDigest(
                organisationId: $organisationId,
                registrationDays: $registrationDays,
                serviceDays: $serviceDays,
                serviceReadingThreshold: $serviceReadingThreshold,
                timezone: $timezone,
                branchIds: $branchScopeEnabled ? $accessibleBranchIds : null
            );

            if (!$regoEnabled) {
                $digest['registration'] = ['overdue' => [], 'due_soon' => []];
            }

            if (!$serviceEnabled) {
                $digest['serviceDate'] = ['overdue' => [], 'due_soon' => []];
                $digest['serviceReading'] = ['overdue' => [], 'due_soon' => []];
            }
        }

        return view('sharpfleet.admin.reminders', [
            'timezone' => $timezone,
            'dateFormat' => $dateFormat,
            'regoEnabled' => $regoEnabled,
            'serviceEnabled' => $serviceEnabled,
            'recipient' => $recipient,
            'digest' => $digest,
        ]);
    }

    public function vehicles(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReminders($user)) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'Missing organisation');
        }

        $branchService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchScopeEnabled = !$bypassBranchRestrictions
            && $branchService->branchesEnabled()
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) ($user['id'] ?? 0))
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $settingsService = new CompanySettingsService($organisationId);
        $regoEnabled = $settingsService->vehicleRegistrationTrackingEnabled();
        $serviceEnabled = $settingsService->vehicleServicingTrackingEnabled();

        $registrationDays = $settingsService->reminderRegistrationDays();
        $serviceDays = $settingsService->reminderServiceDays();
        $serviceReadingThreshold = $settingsService->reminderServiceReadingThreshold();

        $digest = [
            'registration' => ['overdue' => [], 'due_soon' => []],
            'serviceDate' => ['overdue' => [], 'due_soon' => []],
            'serviceReading' => ['overdue' => [], 'due_soon' => []],
        ];

        if ($regoEnabled || $serviceEnabled) {
            $digest = (new VehicleReminderService())->buildDigest(
                organisationId: $organisationId,
                registrationDays: $registrationDays,
                serviceDays: $serviceDays,
                serviceReadingThreshold: $serviceReadingThreshold,
                timezone: $settingsService->timezone(),
                branchIds: $branchScopeEnabled ? $accessibleBranchIds : null
            );

            if (!$regoEnabled) {
                $digest['registration'] = ['overdue' => [], 'due_soon' => []];
            }

            if (!$serviceEnabled) {
                $digest['serviceDate'] = ['overdue' => [], 'due_soon' => []];
                $digest['serviceReading'] = ['overdue' => [], 'due_soon' => []];
            }
        }

        $vehicleIds = collect([
            ...array_column($digest['registration']['overdue'] ?? [], 'vehicle_id'),
            ...array_column($digest['registration']['due_soon'] ?? [], 'vehicle_id'),
            ...array_column($digest['serviceDate']['overdue'] ?? [], 'vehicle_id'),
            ...array_column($digest['serviceDate']['due_soon'] ?? [], 'vehicle_id'),
            ...array_column($digest['serviceReading']['overdue'] ?? [], 'vehicle_id'),
            ...array_column($digest['serviceReading']['due_soon'] ?? [], 'vehicle_id'),
        ])->filter(fn ($v) => (int) $v > 0)->unique()->values()->all();

        $vehicles = collect();
        if (count($vehicleIds) > 0) {
            $select = ['id', 'name', 'registration_number', 'tracking_mode'];
            if (Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')) {
                $select[] = 'branch_id';
            }

            $vehicles = DB::connection('sharpfleet')
                ->table('vehicles')
                ->select($select)
                ->where('organisation_id', $organisationId)
                ->whereIn('id', $vehicleIds)
                ->get()
                ->keyBy('id');
        }

        $branchDistanceUnits = [];
        if (
            Schema::connection('sharpfleet')->hasTable('branches')
            && Schema::connection('sharpfleet')->hasColumn('branches', 'distance_unit')
        ) {
            $branchIds = $vehicles
                ->map(fn ($v) => (int) ($v->branch_id ?? 0))
                ->filter(fn ($v) => $v > 0)
                ->unique()
                ->values()
                ->all();

            if (count($branchIds) > 0) {
                $branchDistanceUnits = DB::connection('sharpfleet')
                    ->table('branches')
                    ->where('organisation_id', $organisationId)
                    ->whereIn('id', $branchIds)
                    ->pluck('distance_unit', 'id')
                    ->map(fn ($v) => strtolower(trim((string) $v)))
                    ->toArray();
            }
        }

        $resolveDistanceUnit = function ($vehicle) use ($settingsService, $branchDistanceUnits) {
            $branchId = (int) ($vehicle->branch_id ?? 0);
            if ($branchId > 0 && isset($branchDistanceUnits[$branchId])) {
                $unit = $branchDistanceUnits[$branchId];
                if (in_array($unit, ['km', 'mi'], true)) {
                    return $unit;
                }
            }

            return $settingsService->distanceUnitForBranch($branchId);
        };

        $items = [];

        foreach (['overdue', 'due_soon'] as $status) {
            foreach ($digest['registration'][$status] ?? [] as $item) {
                $vehicle = $vehicles[$item['vehicle_id']] ?? null;
                $days = (int) ($item['days'] ?? 0);
                $items[] = [
                    'vehicle_id' => (int) ($item['vehicle_id'] ?? 0),
                    'vehicle_name' => $vehicle ? (string) ($vehicle->name ?? '') : (string) ($item['name'] ?? ''),
                    'registration_number' => $vehicle ? (string) ($vehicle->registration_number ?? '') : (string) ($item['registration_number'] ?? ''),
                    'status' => $status,
                    'label' => $status === 'overdue'
                        ? "Registration overdue by {$days} day(s)"
                        : "Registration due in {$days} day(s)",
                ];
            }
        }

        foreach (['overdue', 'due_soon'] as $status) {
            foreach ($digest['serviceDate'][$status] ?? [] as $item) {
                $vehicle = $vehicles[$item['vehicle_id']] ?? null;
                $days = (int) ($item['days'] ?? 0);
                $items[] = [
                    'vehicle_id' => (int) ($item['vehicle_id'] ?? 0),
                    'vehicle_name' => $vehicle ? (string) ($vehicle->name ?? '') : (string) ($item['name'] ?? ''),
                    'registration_number' => $vehicle ? (string) ($vehicle->registration_number ?? '') : '',
                    'status' => $status,
                    'label' => $status === 'overdue'
                        ? "Service (date) overdue by {$days} day(s)"
                        : "Service (date) due in {$days} day(s)",
                ];
            }
        }

        foreach (['overdue', 'due_soon'] as $status) {
            foreach ($digest['serviceReading'][$status] ?? [] as $item) {
                $vehicle = $vehicles[$item['vehicle_id']] ?? null;
                $delta = (int) ($item['delta'] ?? 0);
                $trackingMode = $item['tracking_mode'] ?? ($vehicle->tracking_mode ?? 'distance');
                $unit = $trackingMode === 'hours'
                    ? 'hours'
                    : $resolveDistanceUnit($vehicle ?? (object) []);
                $amount = abs($delta);
                $items[] = [
                    'vehicle_id' => (int) ($item['vehicle_id'] ?? 0),
                    'vehicle_name' => $vehicle ? (string) ($vehicle->name ?? '') : (string) ($item['name'] ?? ''),
                    'registration_number' => $vehicle ? (string) ($vehicle->registration_number ?? '') : '',
                    'status' => $status,
                    'label' => $status === 'overdue'
                        ? "Service (reading) overdue by {$amount} {$unit}"
                        : "Service (reading) due in {$amount} {$unit}",
                ];
            }
        }

        usort($items, function ($a, $b) {
            $priority = ['overdue' => 0, 'due_soon' => 1];
            $pa = $priority[$a['status']] ?? 2;
            $pb = $priority[$b['status']] ?? 2;
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }
            return strcmp((string) $a['vehicle_name'], (string) $b['vehicle_name']);
        });

        return view('sharpfleet.admin.reminders-vehicles', [
            'items' => $items,
            'regoEnabled' => $regoEnabled,
            'serviceEnabled' => $serviceEnabled,
        ]);
    }

    private function resolveSubscriberAdminEmail(int $organisationId): ?string
    {
        try {
            $orgColumns = Schema::connection('sharpfleet')->getColumnListing('organisations');
        } catch (\Throwable $e) {
            $orgColumns = [];
        }

        if (in_array('billing_email', $orgColumns, true)) {
            $billing = DB::connection('sharpfleet')
                ->table('organisations')
                ->where('id', $organisationId)
                ->value('billing_email');

            $billing = is_string($billing) ? trim($billing) : '';
            if ($billing !== '' && filter_var($billing, FILTER_VALIDATE_EMAIL)) {
                return $billing;
            }
        }

        if (!Schema::connection('sharpfleet')->hasTable('users')) {
            return null;
        }

        try {
            $userColumns = Schema::connection('sharpfleet')->getColumnListing('users');
        } catch (\Throwable $e) {
            $userColumns = [];
        }

        if (!in_array('organisation_id', $userColumns, true) || !in_array('email', $userColumns, true) || !in_array('role', $userColumns, true)) {
            return null;
        }

        $adminEmail = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('email');

        $adminEmail = is_string($adminEmail) ? trim($adminEmail) : '';
        if ($adminEmail !== '' && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return $adminEmail;
        }

        return null;
    }
}
