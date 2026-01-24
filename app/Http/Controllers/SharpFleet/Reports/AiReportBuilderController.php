<?php

namespace App\Http\Controllers\SharpFleet\Reports;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\ReportAiClient;
use App\Support\SharpFleet\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AiReportBuilderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        return view('sharpfleet.admin.reports.ai-report-builder', [
            'prompt' => old('prompt', ''),
            'result' => null,
        ]);
    }

    public function generate(Request $request, ReportAiClient $client): View
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
        ]);

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'Missing organisation');
        }

        $settings = new CompanySettingsService($organisationId);
        $intent = $client->parseIntent(trim($validated['prompt']));
        if (!$intent) {
            return back()
                ->withErrors(['prompt' => 'Unable to parse the report request right now. Please try again.'])
                ->withInput();
        }

        $target = $this->resolveTarget($organisationId, $intent['entity_type'], $intent['name'], $settings);
        if ($target === null) {
            return back()
                ->withErrors(['prompt' => 'No matching driver, customer, or vehicle found for that request.'])
                ->withInput();
        }

        $result = $this->buildReport($organisationId, $user, $target, $settings);

        return view('sharpfleet.admin.reports.ai-report-builder', [
            'prompt' => trim($validated['prompt']),
            'result' => $result,
        ]);
    }

    private function resolveTarget(
        int $organisationId,
        string $entityType,
        string $name,
        CompanySettingsService $settings
    ): ?array {
        $name = trim($name);
        $candidates = [$entityType, 'customer', 'vehicle', 'driver'];

        foreach ($candidates as $type) {
            if ($type === 'customer') {
                if (!$this->customerLinkingEnabled($organisationId, $settings)) {
                    continue;
                }

                $customer = DB::connection('sharpfleet')
                    ->table('customers')
                    ->where('organisation_id', $organisationId)
                    ->where('is_active', 1)
                    ->when($name !== '', function ($q) use ($name) {
                        $q->where('name', 'like', '%' . $name . '%');
                    })
                    ->orderBy('name')
                    ->first();

                if ($customer) {
                    return [
                        'type' => 'customer',
                        'id' => (int) $customer->id,
                        'label' => (string) $customer->name,
                    ];
                }
            }

            if ($type === 'vehicle') {
                $vehicle = DB::connection('sharpfleet')
                    ->table('vehicles')
                    ->where('organisation_id', $organisationId)
                    ->where('is_active', 1)
                    ->when($name !== '', function ($q) use ($name) {
                        $q->where(function ($sub) use ($name) {
                            $sub->where('name', 'like', '%' . $name . '%')
                                ->orWhere('registration_number', 'like', '%' . $name . '%');
                        });
                    })
                    ->orderBy('name')
                    ->first();

                if ($vehicle) {
                    return [
                        'type' => 'vehicle',
                        'id' => (int) $vehicle->id,
                        'label' => trim((string) $vehicle->name . ' ' . (string) ($vehicle->registration_number ?? '')),
                    ];
                }
            }

            if ($type === 'driver') {
                $driver = DB::connection('sharpfleet')
                    ->table('users')
                    ->where('organisation_id', $organisationId)
                    ->where('is_driver', 1)
                    ->whereNull('archived_at')
                    ->where('is_active', 1)
                    ->when($name !== '', function ($q) use ($name) {
                        $q->where(function ($sub) use ($name) {
                            $sub->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $name . '%')
                                ->orWhere('first_name', 'like', '%' . $name . '%')
                                ->orWhere('last_name', 'like', '%' . $name . '%');
                        });
                    })
                    ->orderBy('first_name')
                    ->first();

                if ($driver) {
                    return [
                        'type' => 'driver',
                        'id' => (int) $driver->id,
                        'label' => trim((string) $driver->first_name . ' ' . (string) $driver->last_name),
                    ];
                }
            }
        }

        return null;
    }

    private function buildReport(
        int $organisationId,
        array $user,
        array $target,
        CompanySettingsService $settings
    ): array {
        $reporting = $settings->all()['reporting'] ?? [];
        $includePrivateTrips = (bool) ($reporting['include_private_trips'] ?? true);

        [$startDate, $endDate] = $this->resolveDateRange(
            $settings->timezone(),
            (string) ($reporting['default_date_range'] ?? 'month_to_date')
        );

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        $branchScopeEnabled = $branchAccessEnabled && !Roles::bypassesBranchRestrictions($user);
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) ($user['id'] ?? 0))
            : [];

        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $customerLinkingEnabled = $this->customerLinkingEnabled($organisationId, $settings);
        $hasVehicleBranchId = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');

        $query = DB::connection('sharpfleet')
            ->table('trips')
            ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->join('users', 'trips.user_id', '=', 'users.id');

        if ($customerLinkingEnabled) {
            $query->leftJoin('customers', 'trips.customer_id', '=', 'customers.id');
        }

        $query->select(
            'trips.*',
            DB::raw("COALESCE(vehicles.name, 'Private vehicle') as vehicle_name"),
            DB::raw("COALESCE(vehicles.registration_number, '') as registration_number"),
            DB::raw("COALESCE(vehicles.tracking_mode, 'distance') as tracking_mode"),
            $hasVehicleBranchId ? 'vehicles.branch_id as vehicle_branch_id' : DB::raw('NULL as vehicle_branch_id'),
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name"),
            $customerLinkingEnabled
                ? DB::raw('COALESCE(customers.name, trips.customer_name) as customer_name_display')
                : DB::raw('trips.customer_name as customer_name_display')
        );

        $query->where('trips.organisation_id', $organisationId);

        if ($branchScopeEnabled && count($accessibleBranchIds) > 0) {
            $query->where(function ($sub) use ($accessibleBranchIds) {
                $sub->whereNull('trips.vehicle_id')
                    ->orWhereIn('vehicles.branch_id', $accessibleBranchIds);
            });
        }

        if (!$includePrivateTrips) {
            $query->where(function ($q) {
                $q->whereNull('trips.trip_mode')
                    ->orWhere('trips.trip_mode', '!=', 'private');
            });
        }

        if ($startDate) {
            $query->where('trips.started_at', '>=', $startDate->toDateTimeString());
        }
        if ($endDate) {
            $query->where('trips.started_at', '<=', $endDate->toDateTimeString());
        }

        if ($target['type'] === 'driver') {
            $query->where('trips.user_id', (int) $target['id']);
        } elseif ($target['type'] === 'vehicle') {
            $query->where('trips.vehicle_id', (int) $target['id']);
        } elseif ($target['type'] === 'customer') {
            $query->where(function ($sub) use ($target) {
                $sub->where('trips.customer_id', (int) $target['id'])
                    ->orWhere('trips.customer_name', (string) $target['label']);
            });
        }

        $trips = $query
            ->orderByDesc('trips.started_at')
            ->get();

        $displayTrips = [];
        $totalTrips = $trips->count();
        $totalSeconds = 0;
        $distanceTotals = ['km' => 0.0, 'mi' => 0.0];
        $vehicleCounts = [];
        $purposeCounts = [];
        $customerCounts = [];
        $companyUnit = $settings->distanceUnit();

        $branchUnits = $this->loadBranchUnits($organisationId, $trips);
        $dateFormat = $settings->dateFormat();
        $timeFormat = $settings->timeFormat();
        $purposeEnabled = $settings->purposeOfTravelEnabled();

        foreach ($trips as $trip) {
            $totalSeconds += $this->tripSeconds($trip);

            $distanceDelta = $this->distanceDelta($trip);
            $unit = $this->resolveTripUnit($trip, $settings, $branchUnits, $companyUnit);

            if ($distanceDelta > 0 && $unit !== 'hours') {
                if ($unit === 'mi') {
                    $distanceTotals['mi'] += $distanceDelta;
                } else {
                    $distanceTotals['km'] += $distanceDelta;
                }
            }

            $vehicleName = (string) ($trip->vehicle_name ?? 'Unknown vehicle');
            $vehicleCounts[$vehicleName] = ($vehicleCounts[$vehicleName] ?? 0) + 1;

            if ($customerLinkingEnabled) {
                $customerName = trim((string) ($trip->customer_name_display ?? ''));
                if ($customerName !== '') {
                    $customerCounts[$customerName] = ($customerCounts[$customerName] ?? 0) + 1;
                }
            }

            if ($purposeEnabled) {
                $purpose = trim((string) ($trip->purpose_of_travel ?? ''));
                if ($purpose !== '') {
                    $purposeCounts[$purpose] = ($purposeCounts[$purpose] ?? 0) + 1;
                }
            }

            $displayTrips[] = [
                'started_at' => $this->formatDateTime($trip->started_at, $settings->timezone(), $dateFormat, $timeFormat),
                'ended_at' => $this->formatDateTime($trip->end_time ?? $trip->ended_at ?? null, $settings->timezone(), $dateFormat, $timeFormat),
                'vehicle' => $vehicleName,
                'distance' => $this->formatDistance($distanceDelta, $unit),
                'duration' => $this->formatDuration($this->tripSeconds($trip)),
                'purpose' => $purposeEnabled ? (string) ($trip->purpose_of_travel ?? '') : '',
                'customer' => $customerLinkingEnabled ? (string) ($trip->customer_name_display ?? '') : '',
            ];
        }

        $topVehicle = $this->maxKey($vehicleCounts);
        $topCustomer = $customerLinkingEnabled ? $this->maxKey($customerCounts) : null;
        $topPurpose = $purposeEnabled ? $this->maxKey($purposeCounts) : null;

        $distanceLabel = $this->formatTotalDistance($distanceTotals, $companyUnit);

        $dateRangeLabel = 'All time';
        if ($startDate && $endDate) {
            $dateRangeLabel = $startDate->copy()->timezone($settings->timezone())->format($dateFormat)
                . ' - ' . $endDate->copy()->timezone($settings->timezone())->format($dateFormat);
        }

        return [
            'title' => 'Trips Report for ' . $target['label'],
            'subtitle' => 'Based on ' . $target['type'] . ' matching "' . $target['label'] . '"',
            'date_range' => $dateRangeLabel,
            'totals' => [
                'total_trips' => $totalTrips,
                'total_distance' => $distanceLabel,
                'total_drive_time' => $this->formatDuration($totalSeconds),
            ],
            'vehicles_used' => array_keys($vehicleCounts),
            'vehicle_used_most' => $topVehicle,
            'purpose' => $purposeEnabled ? ($topPurpose ?: 'Not captured') : 'Not enabled',
            'top_customer' => $customerLinkingEnabled ? ($topCustomer ?: 'None') : 'Not enabled',
            'trips' => $displayTrips,
        ];
    }

    private function resolveDateRange(string $companyTimezone, string $defaultRule): array
    {
        $start = null;
        $end = null;

        $now = Carbon::now($companyTimezone);
        $rule = strtolower(trim($defaultRule));
        if ($rule === 'last_30_days') {
            $start = $now->copy()->subDays(29)->startOfDay();
            $end = $now->copy()->endOfDay();
        } else {
            $start = $now->copy()->startOfMonth()->startOfDay();
            $end = $now->copy()->endOfDay();
        }

        $appTz = (string) (config('app.timezone') ?: 'UTC');
        return [
            $start ? $start->copy()->setTimezone($appTz) : null,
            $end ? $end->copy()->setTimezone($appTz) : null,
        ];
    }

    private function customerLinkingEnabled(int $organisationId, CompanySettingsService $settings): bool
    {
        return (bool) ($settings->all()['customer']['enabled'] ?? false)
            && Schema::connection('sharpfleet')->hasTable('customers')
            && Schema::connection('sharpfleet')->hasColumn('trips', 'customer_id');
    }

    private function loadBranchUnits(int $organisationId, $trips): array
    {
        if (!$trips || $trips->count() === 0) {
            return [];
        }

        if (!Schema::connection('sharpfleet')->hasTable('branches')
            || !Schema::connection('sharpfleet')->hasColumn('branches', 'distance_unit')
        ) {
            return [];
        }

        $branchIds = $trips->map(function ($t) {
            $vehicleBranchId = isset($t->vehicle_branch_id) ? (int) ($t->vehicle_branch_id ?? 0) : 0;
            $tripBranchId = isset($t->branch_id) ? (int) ($t->branch_id ?? 0) : 0;
            return $vehicleBranchId > 0 ? $vehicleBranchId : $tripBranchId;
        })
            ->filter(fn ($id) => is_int($id) && $id > 0)
            ->unique()
            ->values()
            ->all();

        if (count($branchIds) === 0) {
            return [];
        }

        return DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId)
            ->whereIn('id', $branchIds)
            ->pluck('distance_unit', 'id')
            ->map(fn ($v) => strtolower(trim((string) $v)))
            ->toArray();
    }

    private function resolveTripUnit($trip, CompanySettingsService $settings, array $branchUnits, string $companyUnit): string
    {
        $trackingMode = (string) ($trip->tracking_mode ?? 'distance');
        if ($trackingMode === 'hours') {
            return 'hours';
        }

        $vehicleBranchId = isset($trip->vehicle_branch_id) ? (int) ($trip->vehicle_branch_id ?? 0) : 0;
        $tripBranchId = isset($trip->branch_id) ? (int) ($trip->branch_id ?? 0) : 0;
        $resolvedBranchId = $vehicleBranchId > 0 ? $vehicleBranchId : $tripBranchId;

        if ($resolvedBranchId > 0 && isset($branchUnits[$resolvedBranchId])) {
            $candidate = (string) $branchUnits[$resolvedBranchId];
            if (in_array($candidate, ['km', 'mi'], true)) {
                return $candidate;
            }
        }

        $fallback = $settings->distanceUnitForBranch($resolvedBranchId > 0 ? $resolvedBranchId : null);
        if (in_array($fallback, ['km', 'mi'], true)) {
            return $fallback;
        }

        return in_array($companyUnit, ['km', 'mi'], true) ? $companyUnit : 'km';
    }

    private function distanceDelta($trip): float
    {
        if (!isset($trip->start_km, $trip->end_km)) {
            return 0.0;
        }

        $start = (float) $trip->start_km;
        $end = (float) $trip->end_km;
        if ($end < $start) {
            return 0.0;
        }

        return $end - $start;
    }

    private function tripSeconds($trip): int
    {
        $start = $trip->started_at ?? null;
        $end = $trip->end_time ?? $trip->ended_at ?? null;

        if (!$start || !$end) {
            return 0;
        }

        try {
            return Carbon::parse($start)->diffInSeconds(Carbon::parse($end));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function formatDuration(int $seconds): string
    {
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        return $hours . 'h ' . $minutes . 'm';
    }

    private function formatDistance(float $delta, string $unit): string
    {
        if ($unit === 'hours') {
            return number_format($delta, 1) . ' hrs';
        }

        return number_format($delta, 1) . ' ' . $unit;
    }

    private function formatTotalDistance(array $distanceTotals, string $companyUnit): string
    {
        $hasKm = (float) ($distanceTotals['km'] ?? 0) > 0;
        $hasMi = (float) ($distanceTotals['mi'] ?? 0) > 0;

        if ($hasKm && $hasMi) {
            return number_format((float) $distanceTotals['km'], 1) . ' km / ' .
                number_format((float) $distanceTotals['mi'], 1) . ' mi';
        }

        if ($hasMi) {
            return number_format((float) $distanceTotals['mi'], 1) . ' mi';
        }

        $unit = in_array($companyUnit, ['km', 'mi'], true) ? $companyUnit : 'km';
        return number_format((float) $distanceTotals['km'], 1) . ' ' . $unit;
    }

    private function formatDateTime(?string $value, string $timezone, string $dateFormat, string $timeFormat): string
    {
        if (!$value) {
            return '—';
        }

        try {
            return Carbon::parse($value)->timezone($timezone)->format($dateFormat . ' ' . $timeFormat);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function maxKey(array $items): ?string
    {
        if (empty($items)) {
            return null;
        }

        arsort($items);
        $keys = array_keys($items);
        return $keys[0] ?? null;
    }
}
