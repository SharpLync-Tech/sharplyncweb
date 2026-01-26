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

        // Distance units are always resolved from the originating branch (with company fallback).

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

        $hasVehicleBranchId = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');
        $hasPrivateVehicleFlag = Schema::connection('sharpfleet')->hasColumn('trips', 'is_private_vehicle');

        $branchesForUi = collect();
        $showBranchFilter = false;
        if ($branchesEnabled && $hasVehicleBranchId) {
            if ($branchAccessEnabled) {
                $branchesForUi = $branchesService->getBranchesForUser($organisationId, (int) $actor['id']);
            } else {
                $branchesForUi = $branchesService->getBranches($organisationId);
            }

            // Only show the branch selector when the user can actually choose between branches.
            $showBranchFilter = $branchesForUi->count() > 1;
        }

        $allowBranchOverride = (bool) $allowOverrides;
        $selectedBranchIds = [];

        if ($showBranchFilter && $allowBranchOverride) {
            $raw = $request->input('branch_ids', []);
            $raw = is_array($raw) ? $raw : [$raw];

            // Treat "all" or empty selection as "all branches".
            $raw = array_values(array_filter($raw, fn ($v) => $v !== null && $v !== '' && $v !== 'all'));
            $selectedBranchIds = array_values(array_unique(array_filter(array_map(function ($v) {
                if (is_numeric($v)) {
                    $id = (int) $v;
                    return $id > 0 ? $id : null;
                }
                return null;
            }, $raw))));

            if ($branchAccessEnabled && count($selectedBranchIds) > 0) {
                $selectedBranchIds = array_values(array_intersect($selectedBranchIds, $accessibleBranchIds));
            }

            // If user selection ends up empty after validation, fall back to "all".
            if (count($selectedBranchIds) === 0) {
                $selectedBranchIds = [];
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
            ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->join('users', 'trips.user_id', '=', 'users.id');

        if ($branchAccessEnabled) {
            $query->where(function ($sub) use ($accessibleBranchIds) {
                $sub->whereNull('trips.vehicle_id')
                    ->orWhereIn('vehicles.branch_id', $accessibleBranchIds);
            });
        }

        if ($showBranchFilter && count($selectedBranchIds) > 0) {
            $query->where(function ($sub) use ($selectedBranchIds) {
                $sub->whereNull('trips.vehicle_id')
                    ->orWhereIn('vehicles.branch_id', $selectedBranchIds);
            });
        }

        if ($customerLinkingEnabled) {
            $query->leftJoin('customers', 'trips.customer_id', '=', 'customers.id');
        }

        $query->select(
            'trips.*',
            DB::raw("COALESCE(vehicles.name, 'Private vehicle') as vehicle_name"),
            DB::raw("COALESCE(vehicles.registration_number, '') as registration_number"),
            DB::raw("COALESCE(vehicles.tracking_mode, 'distance') as tracking_mode"),
            $hasVehicleBranchId ? 'vehicles.branch_id as vehicle_branch_id' : DB::raw('NULL as vehicle_branch_id'),
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
            $query->where(function ($q) use ($hasPrivateVehicleFlag) {
                $q->where(function ($sub) {
                    $sub->whereNull('trips.trip_mode')
                        ->orWhere('trips.trip_mode', '!=', 'private');
                });
                if ($hasPrivateVehicleFlag) {
                    $q->where(function ($sub) {
                        $sub->whereNull('trips.is_private_vehicle')
                            ->orWhere('trips.is_private_vehicle', 0);
                    });
                }
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

        [$trips, $totals] = $this->applyBranchDistanceUnits($organisationId, $trips, $settingsService);

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

        $branchLabel = 'All branches';
        if ($showBranchFilter && count($selectedBranchIds) > 0) {
            $nameById = $branchesForUi->keyBy('id');
            $names = [];
            foreach ($selectedBranchIds as $bid) {
                $row = $nameById->get($bid);
                $names[] = $row ? (string) ($row->name ?? ('Branch #' . $bid)) : ('Branch #' . $bid);
            }
            $branchLabel = implode(', ', $names);
        }

        $applied = [
            'private_trips_included' => $includePrivateTrips,
            // View-friendly keys
            'include_private_trips' => $includePrivateTrips,
            'customer_linking_enabled' => $customerLinkingEnabled,
            'date_range_label' => $dateRangeLabel,
            'vehicle_label' => $selectedVehicleLabel,
            'customer_label' => $selectedCustomerLabel,
            'branch_filter_enabled' => $showBranchFilter,
            'branch_label' => $showBranchFilter ? $branchLabel : '—',
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
            'branch_ids' => $selectedBranchIds,
            // View-friendly flags
            'allow_vehicle_override' => $allowVehicleOverride,
            'allow_date_override' => $allowDateOverride,
            'allow_customer_override' => $allowCustomerOverride,
            'allow_branch_override' => $allowBranchOverride,
            'show_customer_filter' => $customerLinkingEnabled,
            'show_branch_filter' => $showBranchFilter,
            'controls_enabled' => [
                'vehicle' => $allowVehicleOverride,
                'date' => $allowDateOverride,
                'customer' => $allowCustomerOverride,
                'branch' => $allowBranchOverride,
            ],
        ];

        return [
            'applied' => $applied,
            'ui' => $ui,
            'trips' => $trips,
            'totals' => $totals,
            'customers' => $customers,
            'branches' => $branchesForUi,
            'hasCustomersTable' => $hasCustomersTable,
            'customerLinkingEnabled' => $customerLinkingEnabled,
            'companyTimezone' => $companyTimezone,
        ];
    }

    /**
     * Apply branch-resolved distance units to trip rows and totals.
     *
     * Rules:
     * - Each trip row uses the unit of its originating branch.
     * - If branch unit is not configured, fall back to the company default.
     * - If both are unavailable, fall back to kilometres (km).
     *
     * Notes:
     * - trips.start_km/end_km are stored in kilometres for distance tracking.
     * - Engine-hours mode is not converted.
     *
     * @return array{0: Collection<int, object>, 1: array<string, mixed>}
     */
    private function applyBranchDistanceUnits(int $organisationId, Collection $trips, CompanySettingsService $settingsService): array
    {
        $companyUnit = $this->resolveCompanyDefaultDistanceUnit($organisationId, $settingsService);

        $branchUnits = [];
        $hasBranchesTable = Schema::connection('sharpfleet')->hasTable('branches');
        $hasBranchDistanceUnitColumn = $hasBranchesTable && Schema::connection('sharpfleet')->hasColumn('branches', 'distance_unit');

        if ($hasBranchDistanceUnitColumn) {
            $branchIds = $trips
                ->map(function ($t) {
                    $vehicleBranchId = isset($t->vehicle_branch_id) ? (int) ($t->vehicle_branch_id ?? 0) : 0;
                    $tripBranchId = isset($t->branch_id) ? (int) ($t->branch_id ?? 0) : 0;
                    return $vehicleBranchId > 0 ? $vehicleBranchId : $tripBranchId;
                })
                ->filter(fn ($id) => is_int($id) && $id > 0)
                ->unique()
                ->values();

            if ($branchIds->count() > 0) {
                $branchUnits = DB::connection('sharpfleet')
                    ->table('branches')
                    ->where('organisation_id', $organisationId)
                    ->whereIn('id', $branchIds->all())
                    ->pluck('distance_unit', 'id')
                    ->map(fn ($v) => strtolower(trim((string) $v)))
                    ->toArray();
            }
        }

        $totals = [
            // Backwards compatible canonical totals (km distance + hours)
            'km' => 0.0,
            'hours' => 0.0,

            // Display totals (mixed is allowed/expected)
            'distance_km' => 0.0,
            'distance_mi' => 0.0,
        ];

        $mapped = $trips->map(function ($trip) use ($settingsService, $companyUnit, $branchUnits, &$totals) {
            $trackingMode = (string) ($trip->tracking_mode ?? 'distance');
            $isHours = ($trackingMode === 'hours');

            // Default row fields
            $trip->display_unit = $isHours ? 'hours' : 'km';
            $trip->display_start = $trip->start_km ?? null;
            $trip->display_end = $trip->end_km ?? null;

            if (!isset($trip->end_km, $trip->start_km) || $trip->end_km === null || $trip->start_km === null) {
                return $trip;
            }

            $delta = (float) $trip->end_km - (float) $trip->start_km;
            if ($delta < 0) {
                return $trip;
            }

            if ($isHours) {
                $totals['hours'] += $delta;
                return $trip;
            }

            // Canonical km totals (distance)
            $totals['km'] += $delta;

            $vehicleBranchId = isset($trip->vehicle_branch_id) ? (int) ($trip->vehicle_branch_id ?? 0) : 0;
            $tripBranchId = isset($trip->branch_id) ? (int) ($trip->branch_id ?? 0) : 0;
            $resolvedBranchId = $vehicleBranchId > 0 ? $vehicleBranchId : $tripBranchId;

            $branchUnit = null;
            if ($resolvedBranchId > 0 && isset($branchUnits[$resolvedBranchId])) {
                $candidate = (string) $branchUnits[$resolvedBranchId];
                if (in_array($candidate, ['km', 'mi'], true)) {
                    $branchUnit = $candidate;
                }
            }

            // Backwards-compatible fallback to JSON-based unit settings (if branch table column is absent).
            if ($branchUnit === null && $resolvedBranchId > 0) {
                $candidate = $settingsService->distanceUnitForBranch($resolvedBranchId);
                if (in_array($candidate, ['km', 'mi'], true)) {
                    $branchUnit = $candidate;
                }
            }

            $rowUnit = $branchUnit ?: $companyUnit;
            if (!in_array($rowUnit, ['km', 'mi'], true)) {
                $rowUnit = 'km';
            }

            $trip->display_unit = $rowUnit;

            // No automatic conversion: stored readings are treated as already in the branch's local unit.
            $trip->display_start = $trip->start_km;
            $trip->display_end = $trip->end_km;

            // Totals for display
            if ($rowUnit === 'mi') {
                $totals['distance_mi'] += $delta;
            } else {
                $totals['distance_km'] += $delta;
            }

            return $trip;
        });

        // Helpful single-branch summary values (when the report ends up being single-unit).
        $hasKm = (float) ($totals['distance_km'] ?? 0) > 0;
        $hasMi = (float) ($totals['distance_mi'] ?? 0) > 0;
        if ($hasKm && !$hasMi) {
            $totals['distance'] = (float) $totals['distance_km'];
            $totals['distance_unit'] = 'km';
        } elseif ($hasMi && !$hasKm) {
            $totals['distance'] = (float) $totals['distance_mi'];
            $totals['distance_unit'] = 'mi';
        }

        return [$mapped, $totals];
    }

    private function resolveCompanyDefaultDistanceUnit(int $organisationId, CompanySettingsService $settingsService): string
    {
        $unit = null;

        if (
            Schema::connection('sharpfleet')->hasTable('companies')
            && Schema::connection('sharpfleet')->hasColumn('companies', 'default_distance_unit')
        ) {
            $query = DB::connection('sharpfleet')->table('companies')->select('default_distance_unit');

            $scoped = false;

            if (Schema::connection('sharpfleet')->hasColumn('companies', 'organisation_id')) {
                $query->where('organisation_id', $organisationId);
                $scoped = true;
            } elseif (Schema::connection('sharpfleet')->hasColumn('companies', 'id')) {
                $query->where('id', $organisationId);
                $scoped = true;
            }

            if ($scoped) {
                $unit = strtolower(trim((string) $query->value('default_distance_unit')));
            }
        }

        if (!in_array($unit, ['km', 'mi'], true)) {
            $unit = strtolower(trim((string) $settingsService->distanceUnit()));
        }

        if (!in_array($unit, ['km', 'mi'], true)) {
            $unit = 'km';
        }

        return $unit;
    }

    private function kmToMi(float $km): float
    {
        return $km * 0.621371;
    }

    /**
     * Stream a CSV export using the same settings-driven report dataset.
     */
    public function streamTripReportCsv(int $organisationId, Request $request, ?array $actor = null)
    {
        $result = $this->buildTripReport($organisationId, $request, $actor);
        /** @var Collection<int, object> $trips */
        $trips = $result['trips'];

        $companyTimezone = (string) ($result['companyTimezone'] ?? (new CompanySettingsService($organisationId))->timezone());
        $appTimezone = (string) (config('app.timezone') ?: 'UTC');

        $dateFormat = str_starts_with($companyTimezone, 'America/')
            ? 'm/d/Y'
            : 'd/m/Y';

        // CSV includes time; keep it consistent and accountant-friendly.
        $dateTimeFormat = $dateFormat . ' H:i';

        $customerLinkingEnabled = (bool) ($result['customerLinkingEnabled'] ?? false);
        $purposeOfTravelEnabled = (new CompanySettingsService($organisationId))->purposeOfTravelEnabled();

        $headers = [
            'Vehicle',
            'Registration',
            'Driver',
            'Trip Mode',
            $customerLinkingEnabled ? 'Customer' : null,
            $purposeOfTravelEnabled ? 'Purpose of Travel' : null,
            'Start Reading',
            'End Reading',
            'Client Present',
            'Client Address',
            'Started At',
            'Ended At',
        ];
        $headers = array_values(array_filter($headers, fn ($h) => $h !== null));

        $callback = function () use (
            $trips,
            $headers,
            $customerLinkingEnabled,
            $purposeOfTravelEnabled,
            $companyTimezone,
            $appTimezone,
            $dateTimeFormat
        ) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($trips as $trip) {
                $unit = isset($trip->display_unit)
                    ? (string) $trip->display_unit
                    : ((($trip->tracking_mode ?? 'distance') === 'hours') ? 'hours' : 'km');

                $startReading = isset($trip->display_start) ? $trip->display_start : $trip->start_km;
                $endReading = isset($trip->display_end) ? $trip->display_end : $trip->end_km;

                $startReadingText = ($startReading === null || $startReading === '') ? '' : ((string) $startReading . ' ' . $unit);
                $endReadingText = ($endReading === null || $endReading === '') ? '' : ((string) $endReading . ' ' . $unit);

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

                $clientPresent = ($trip->client_present === null) ? '' : ($trip->client_present ? 'Yes' : 'No');
                $clientAddress = $trip->client_address ?? 'N/A';

                $startedAtText = '';
                if (!empty($trip->started_at)) {
                    $startedAtText = Carbon::parse($trip->started_at, $appTimezone)
                        ->timezone($companyTimezone)
                        ->format($dateTimeFormat);
                }

                // Prefer end_time (actual end), fall back to ended_at.
                $endValue = $trip->end_time ?? $trip->ended_at ?? null;
                $endedAtText = '';
                if (!empty($endValue)) {
                    $endedAtText = Carbon::parse($endValue, $appTimezone)
                        ->timezone($companyTimezone)
                        ->format($dateTimeFormat);
                }

                $row = array_merge($row, [
                    $startReadingText,
                    $endReadingText,
                    $clientPresent,
                    $clientAddress,
                    $startedAtText,
                    $endedAtText,
                ]);

                fputcsv($file, $row);
            }

            fclose($file);
        };

        $todayLocal = Carbon::now($companyTimezone)->format('Y-m-d');

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="trips_' . $todayLocal . '.csv"',
        ]);
    }

    /**
     * Stream a CSV export for the Client Transport Report.
     */
    public function streamClientTransportCsv(int $organisationId, Request $request, ?array $actor = null)
    {
        $result = $this->buildTripReport($organisationId, $request, $actor);
        /** @var Collection<int, object> $trips */
        $trips = $result['trips'];

        $companyTimezone = (string) ($result['companyTimezone'] ?? (new CompanySettingsService($organisationId))->timezone());
        $appTimezone = (string) (config('app.timezone') ?: 'UTC');

        $dateFormat = str_starts_with($companyTimezone, 'America/')
            ? 'm/d/Y'
            : 'd/m/Y';

        $dateTimeFormat = $dateFormat . ' H:i';
        $timeFormat = 'H:i';

        $purposeOfTravelEnabled = (new CompanySettingsService($organisationId))->purposeOfTravelEnabled();

        $headers = [
            'Client Name',
            'Date/Time',
            'Start Time',
            'End Time',
            'Vehicle',
            'Driver',
            'Trip Purpose',
            'Distance (km)',
        ];

        $callback = function () use (
            $trips,
            $headers,
            $companyTimezone,
            $appTimezone,
            $dateTimeFormat,
            $timeFormat,
            $purposeOfTravelEnabled
        ) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($trips as $trip) {
                $start = $trip->started_at ?? null;
                $endValue = $trip->end_time ?? $trip->ended_at ?? null;

                $dateTimeText = '';
                $startTimeText = '';
                $endTimeText = '';

                if (!empty($start)) {
                    $startCarbon = Carbon::parse($start, $appTimezone)->timezone($companyTimezone);
                    $dateTimeText = $startCarbon->format($dateTimeFormat);
                    $startTimeText = $startCarbon->format($timeFormat);
                }

                if (!empty($endValue)) {
                    $endCarbon = Carbon::parse($endValue, $appTimezone)->timezone($companyTimezone);
                    $endTimeText = $endCarbon->format($timeFormat);
                }

                $distanceText = '';
                if (isset($trip->start_km, $trip->end_km) && is_numeric($trip->start_km) && is_numeric($trip->end_km)) {
                    $delta = (float) $trip->end_km - (float) $trip->start_km;
                    if ($delta >= 0) {
                        $distanceText = number_format($delta, 1);
                    }
                }

                $tripPurpose = '';
                if ($purposeOfTravelEnabled) {
                    $rawMode = strtolower((string) ($trip->trip_mode ?? ''));
                    $isBusiness = $rawMode !== 'private';
                    $tripPurpose = $isBusiness ? ($trip->purpose_of_travel ?? '') : '';
                }

                $row = [
                    $trip->customer_name_display ?? '',
                    $dateTimeText,
                    $startTimeText,
                    $endTimeText,
                    $trip->vehicle_name,
                    $trip->driver_name,
                    $tripPurpose,
                    $distanceText,
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        $todayLocal = Carbon::now($companyTimezone)->format('Y-m-d');

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="client_transport_' . $todayLocal . '.csv"',
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
