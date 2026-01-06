<?php

namespace App\Services\SharpFleet;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ReportingService
{
    /**
     * Centralized decision point for SharpFleet reporting.
     *
     * IMPORTANT: Reporting behaviour must be driven by subscriber/company settings stored in
     * company_settings.settings_json (via CompanySettingsService). We do not infer behaviour
     * from what the UI happens to submit.
     */

    /**
     * Build the Trip Report dataset + the applied settings summary.
     *
     * @return array{applied: array<string, mixed>, ui: array<string, mixed>, trips: \Illuminate\Support\Collection<int, object>, totals: array{km: float, hours: float}}
     */
    public function buildTripReport(int $organisationId, Request $request, ?array $actor = null): array
    {
        $settingsService = new CompanySettingsService($organisationId);
        $settings = $settingsService->all();

        $companyTimezone = $settingsService->timezone();

        $reporting = is_array($settings['reporting'] ?? null) ? $settings['reporting'] : [];

        $includePrivateTrips = (bool) ($reporting['include_private_trips'] ?? true);
        $allowOverrides = (bool) ($reporting['allow_overrides'] ?? true);

        $allowVehicleOverride = $allowOverrides && (bool) ($reporting['allow_vehicle_override'] ?? true);
        $allowDateOverride = $allowOverrides && (bool) ($reporting['allow_date_override'] ?? true);
        $allowCustomerOverride = $allowOverrides && (bool) ($reporting['allow_customer_override'] ?? true);

        $maxRangeDays = $reporting['max_date_range_days'] ?? null;
        $maxRangeDays = is_numeric($maxRangeDays) ? (int) $maxRangeDays : null;

        // Linking rules (subscriber-defined)
        $customerCaptureEnabled = (bool) ($settings['customer']['enabled'] ?? false);
        $hasCustomersTable = Schema::connection('sharpfleet')->hasTable('customers');
        $customerLinkingEnabled = $customerCaptureEnabled && $hasCustomersTable;

        $defaultVehicleId = $reporting['default_vehicle_id'] ?? null;
        $defaultVehicleId = is_numeric($defaultVehicleId) ? (int) $defaultVehicleId : null;

        $defaultCustomerId = $reporting['default_customer_id'] ?? null;
        $defaultCustomerId = is_numeric($defaultCustomerId) ? (int) $defaultCustomerId : null;

        $vehicleId = null;
        if ($allowVehicleOverride) {
            $vehicleId = $request->filled('vehicle_id') ? (int) $request->input('vehicle_id') : null;
        } else {
            $vehicleId = $defaultVehicleId;
        }

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled()
            && is_array($actor)
            && isset($actor['id']);
        $accessibleBranchIds = $branchAccessEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) $actor['id'])
            : [];
        if ($branchAccessEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        if ($branchAccessEnabled && $vehicleId) {
            $vehicleAllowed = DB::connection('sharpfleet')
                ->table('vehicles')
                ->where('organisation_id', $organisationId)
                ->where('id', $vehicleId)
                ->whereIn('branch_id', $accessibleBranchIds)
                ->exists();

            if (!$vehicleAllowed) {
                $vehicleId = null;
            }
        }

        $selectedVehicleLabel = 'All vehicles';
        if ($vehicleId) {
            $vehicleRow = DB::connection('sharpfleet')
                ->table('vehicles')
                ->select('name', 'registration_number')
                ->where('organisation_id', $organisationId)
                ->where('id', $vehicleId)
                ->when(
                    $branchAccessEnabled,
                    fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
                )
                ->first();

            if ($vehicleRow) {
                $selectedVehicleLabel = trim((string) $vehicleRow->name . ' (' . (string) $vehicleRow->registration_number . ')');
            } else {
                $selectedVehicleLabel = 'Vehicle #' . $vehicleId;
            }
        }

        [$startDate, $endDate] = $this->resolveDateRange(
            $companyTimezone,
            (string) ($reporting['default_date_range'] ?? 'month_to_date'),
            $allowDateOverride ? (string) $request->input('start_date', '') : '',
            $allowDateOverride ? (string) $request->input('end_date', '') : '',
            $maxRangeDays
        );

        // Customer filter only applies when linking is enabled.
        $customerId = null;
        if ($customerLinkingEnabled) {
            if ($allowCustomerOverride) {
                $customerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
            } else {
                $customerId = $defaultCustomerId;
            }
        }

        $customers = collect();
        if ($customerLinkingEnabled) {
            $customers = DB::connection('sharpfleet')
                ->table('customers')
                ->select('id', 'name')
                ->where('organisation_id', $organisationId)
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();
        }

        $selectedCustomerName = null;
        if ($customerLinkingEnabled && $customerId) {
            $selectedCustomerName = DB::connection('sharpfleet')
                ->table('customers')
                ->where('organisation_id', $organisationId)
                ->where('id', $customerId)
                ->value('name');
        }

        $selectedCustomerLabel = 'All customers';
        if (!$customerLinkingEnabled) {
            $selectedCustomerLabel = '—';
        } elseif ($customerId) {
            $selectedCustomerLabel = $selectedCustomerName ?: ('Customer #' . $customerId);
        }

        $query = DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->join('users', 'trips.user_id', '=', 'users.id');

        if ($branchAccessEnabled) {
            $query->whereIn('vehicles.branch_id', $accessibleBranchIds);
        }

        if ($customerLinkingEnabled) {
            $query->leftJoin('customers', 'trips.customer_id', '=', 'customers.id');
        }

        $query->select(
            'trips.*',
            'vehicles.name as vehicle_name',
            'vehicles.registration_number',
            'vehicles.tracking_mode',
            Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
                ? 'vehicles.assignment_type as vehicle_assignment_type'
                : DB::raw("NULL as vehicle_assignment_type"),
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name"),
            $customerLinkingEnabled
                ? DB::raw('COALESCE(customers.name, trips.customer_name) as customer_name_display')
                : DB::raw('trips.customer_name as customer_name_display')
        );

        $query->where('trips.organisation_id', $organisationId);

        // Settings-driven: private trips included/excluded.
        if (!$includePrivateTrips) {
            $query->where(function ($q) {
                $q->whereNull('trips.trip_mode')->orWhere('trips.trip_mode', '!=', 'private');
            });
        }

        if ($vehicleId) {
            $query->where('trips.vehicle_id', $vehicleId);
        }

        // Date range is applied based on subscriber's rule; request can only override when permitted.
        if ($startDate) {
            $query->where('trips.started_at', '>=', $startDate->toDateTimeString());
        }
        if ($endDate) {
            $query->where('trips.started_at', '<=', $endDate->toDateTimeString());
        }

        if ($customerLinkingEnabled && $customerId) {
            $query->where(function ($sub) use ($customerId, $selectedCustomerName) {
                $sub->where('trips.customer_id', $customerId);
                if ($selectedCustomerName) {
                    // Backwards compatibility for tenants that stored the selected name into trips.customer_name.
                    $sub->orWhere('trips.customer_name', $selectedCustomerName);
                }
            });
        }

        $trips = $query
            ->orderByDesc('trips.started_at')
            ->get();

        $totals = $this->calculateTotals($trips);

        $dateRangeLabel = '—';
        if ($startDate && $endDate) {
            $dateRangeLabel = $startDate->copy()->timezone($companyTimezone)->toDateString() . ' to ' . $endDate->copy()->timezone($companyTimezone)->toDateString();
        } elseif ($startDate) {
            $dateRangeLabel = 'From ' . $startDate->copy()->timezone($companyTimezone)->toDateString();
        } elseif ($endDate) {
            $dateRangeLabel = 'Up to ' . $endDate->copy()->timezone($companyTimezone)->toDateString();
        }

        $overrideNote = null;
        if (!$allowOverrides) {
            $overrideNote = 'Overrides are disabled by company settings.';
        } elseif (!$allowVehicleOverride || !$allowDateOverride || ($customerLinkingEnabled && !$allowCustomerOverride)) {
            $overrideNote = 'Some filters are locked by company settings.';
        }

        $applied = [
            'private_trips_included' => $includePrivateTrips,
            // View-friendly keys
            'include_private_trips' => $includePrivateTrips,
            'customer_linking_enabled' => $customerLinkingEnabled,
            'date_range_label' => $dateRangeLabel,
            'vehicle_label' => $selectedVehicleLabel,
            'customer_label' => $selectedCustomerLabel,
            'override_note' => $overrideNote,
            'date_range' => [
                'start' => $startDate ? $startDate->copy()->timezone($companyTimezone)->toDateString() : null,
                'end' => $endDate ? $endDate->copy()->timezone($companyTimezone)->toDateString() : null,
                'rule' => (string) ($reporting['default_date_range'] ?? 'month_to_date'),
            ],
            'overrides_allowed' => $allowOverrides,
            'overrides' => [
                'vehicle' => $allowVehicleOverride,
                'date' => $allowDateOverride,
                'customer' => $allowCustomerOverride,
            ],
        ];

        $ui = [
            // These are the values the UI should display (even if the request attempted overrides).
            'vehicle_id' => $vehicleId,
            'start_date' => $startDate ? $startDate->copy()->timezone($companyTimezone)->toDateString() : null,
            'end_date' => $endDate ? $endDate->copy()->timezone($companyTimezone)->toDateString() : null,
            'customer_id' => $customerId,
            // View-friendly flags
            'allow_vehicle_override' => $allowVehicleOverride,
            'allow_date_override' => $allowDateOverride,
            'allow_customer_override' => $allowCustomerOverride,
            'show_customer_filter' => $customerLinkingEnabled,
            'controls_enabled' => [
                'vehicle' => $allowVehicleOverride,
                'date' => $allowDateOverride,
                'customer' => $allowCustomerOverride,
            ],
        ];

        return [
            'applied' => $applied,
            'ui' => $ui,
            'trips' => $trips,
            'totals' => $totals,
            'customers' => $customers,
            'hasCustomersTable' => $hasCustomersTable,
            'customerLinkingEnabled' => $customerLinkingEnabled,
            'companyTimezone' => $companyTimezone,
        ];
    }

    /**
     * Stream a CSV export using the same settings-driven report dataset.
     */
    public function streamTripReportCsv(int $organisationId, Request $request, ?array $actor = null)
    {
        $result = $this->buildTripReport($organisationId, $request, $actor);
        /** @var Collection<int, object> $trips */
        $trips = $result['trips'];

        $customerLinkingEnabled = (bool) ($result['customerLinkingEnabled'] ?? false);
        $purposeOfTravelEnabled = (new CompanySettingsService($organisationId))->purposeOfTravelEnabled();

        $headers = [
            'Vehicle',
            'Rego',
            'Driver',
            'Trip Mode',
            $customerLinkingEnabled ? 'Customer' : null,
            $purposeOfTravelEnabled ? 'Purpose of Travel' : null,
            'Unit',
            'Start Reading',
            'End Reading',
            'Client Present',
            'Client Address',
            'Started At',
            'Ended At',
        ];
        $headers = array_values(array_filter($headers, fn ($h) => $h !== null));

        $callback = function () use ($trips, $headers, $customerLinkingEnabled, $purposeOfTravelEnabled) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($trips as $trip) {
                $unit = ($trip->tracking_mode ?? 'distance') === 'hours' ? 'hours' : 'km';

                $row = [
                    $trip->vehicle_name,
                    $trip->registration_number,
                    $trip->driver_name,
                    $trip->trip_mode,
                ];

                if ($customerLinkingEnabled) {
                    $row[] = $trip->customer_name_display ?? '';
                }

                if ($purposeOfTravelEnabled) {
                    $rawMode = strtolower((string) ($trip->trip_mode ?? ''));
                    $isBusiness = $rawMode !== 'private';
                    $row[] = $isBusiness ? ($trip->purpose_of_travel ?? '') : '';
                }

                $row = array_merge($row, [
                    $unit,
                    $trip->start_km,
                    $trip->end_km,
                    $trip->client_present ? 'Yes' : 'No',
                    $trip->client_address ?? 'N/A',
                    $trip->started_at,
                    $trip->ended_at,
                ]);

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="trips_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Resolve start/end range using subscriber rules.
     *
     * - If request dates are allowed and supplied, they will be used (subject to max_range_days if set).
     * - Otherwise we compute the default range from the subscriber's configured rule.
     */
    private function resolveDateRange(
        string $companyTimezone,
        string $defaultRule,
        string $requestedStartDate,
        string $requestedEndDate,
        ?int $maxRangeDays
    ): array {
        $requestedStartDate = trim($requestedStartDate);
        $requestedEndDate = trim($requestedEndDate);

        $start = null;
        $end = null;

        if ($requestedStartDate !== '' || $requestedEndDate !== '') {
            try {
                $start = $requestedStartDate !== ''
                    ? Carbon::parse($requestedStartDate . ' 00:00:00', $companyTimezone)
                    : null;

                $end = $requestedEndDate !== ''
                    ? Carbon::parse($requestedEndDate . ' 23:59:59', $companyTimezone)
                    : null;
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'start_date' => 'Invalid date range.',
                ]);
            }
        }

        // If the caller did not supply (or was not allowed to supply) a date range, use the subscriber default.
        if ($start === null && $end === null) {
            $now = Carbon::now($companyTimezone);

            $rule = strtolower(trim($defaultRule));
            if ($rule === 'last_30_days') {
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
            } else {
                // month_to_date (default)
                $start = $now->copy()->startOfMonth()->startOfDay();
                $end = $now->copy()->endOfDay();
            }
        }

        if ($start && $end && $end->lessThan($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date must be on or after the start date.',
            ]);
        }

        if ($maxRangeDays !== null && $start && $end) {
            $days = $start->diffInDays($end) + 1;
            if ($days > $maxRangeDays) {
                throw ValidationException::withMessages([
                    'end_date' => 'Date range is too large for your reporting settings.',
                ]);
            }
        }

        // Store/query in app timezone (DB values are stored in server/app timezone).
        $appTz = (string) (config('app.timezone') ?: 'UTC');
        $startApp = $start ? $start->copy()->setTimezone($appTz) : null;
        $endApp = $end ? $end->copy()->setTimezone($appTz) : null;

        return [$startApp, $endApp];
    }

    /**
     * Totals are calculated across the result set and respect KM vs engine-hours.
     */
    private function calculateTotals(Collection $trips): array
    {
        $totals = [
            'km' => 0.0,
            'hours' => 0.0,
        ];

        foreach ($trips as $trip) {
            if (!isset($trip->end_km, $trip->start_km) || $trip->end_km === null || $trip->start_km === null) {
                continue;
            }

            $delta = (float) $trip->end_km - (float) $trip->start_km;
            if ($delta < 0) {
                continue;
            }

            $unitKey = ($trip->tracking_mode ?? 'distance') === 'hours' ? 'hours' : 'km';
            $totals[$unitKey] += $delta;
        }

        return $totals;
    }
}
