<?php

namespace App\Http\Controllers\SharpFleet\Reports;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\ReportAiClient;
use App\Support\SharpFleet\Roles;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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

    public function generate(Request $request, ReportAiClient $client): View|RedirectResponse|Response|StreamedResponse
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

        if (!empty($intent['unsupported'])) {
            return back()
                ->withErrors(['prompt' => 'That request is not supported yet. Try a driver, customer, or vehicle name.'])
                ->withInput();
        }

        $entities = is_array($intent['entities'] ?? null) ? $intent['entities'] : [];
        $driverName = is_string($entities['driver'] ?? null) ? trim((string) $entities['driver']) : '';
        $customerName = is_string($entities['customer'] ?? null) ? trim((string) $entities['customer']) : '';
        $vehicleName = is_string($entities['vehicle'] ?? null) ? trim((string) $entities['vehicle']) : '';
        $userName = is_string($entities['user'] ?? null) ? trim((string) $entities['user']) : '';
        $branchName = is_string($entities['branch'] ?? null) ? trim((string) $entities['branch']) : '';

        $filters = is_array($intent['filters'] ?? null) ? $intent['filters'] : [];
        $clientPresent = array_key_exists('client_present', $filters) && is_bool($filters['client_present'])
            ? $filters['client_present']
            : null;
        $hasRegistration = array_key_exists('has_registration', $filters) && is_bool($filters['has_registration'])
            ? $filters['has_registration']
            : null;
        $dateRange = is_array($filters['date_range'] ?? null) ? $filters['date_range'] : [];
        [$fromDate, $toDate] = $this->parseDateRange(
            $dateRange['from'] ?? null,
            $dateRange['to'] ?? null,
            $settings->timezone()
        );

        $targets = [
            'driver' => null,
            'customer' => null,
            'vehicle' => null,
            'user' => null,
            'branch' => null,
        ];

        if ($driverName !== '') {
            $targets['driver'] = $this->resolveTarget($organisationId, 'driver', $driverName, $settings);
        }
        if ($customerName !== '') {
            $targets['customer'] = $this->resolveTarget($organisationId, 'customer', $customerName, $settings);
        }
        if ($vehicleName !== '') {
            $targets['vehicle'] = $this->resolveTarget($organisationId, 'vehicle', $vehicleName, $settings);
        }
        if ($userName !== '') {
            $targets['user'] = $this->resolveTarget($organisationId, 'user', $userName, $settings);
        }
        if ($branchName !== '') {
            $targets['branch'] = $this->resolveTarget($organisationId, 'branch', $branchName, $settings);
        }

        if ($driverName !== '' && $targets['driver'] === null) {
            return back()
                ->withErrors(['prompt' => 'No matching driver found for that request.'])
                ->withInput();
        }
        if ($customerName !== '' && $targets['customer'] === null) {
            return back()
                ->withErrors(['prompt' => 'No matching customer found for that request.'])
                ->withInput();
        }
        if ($vehicleName !== '' && $targets['vehicle'] === null) {
            return back()
                ->withErrors(['prompt' => 'No matching vehicle found for that request.'])
                ->withInput();
        }
        if ($userName !== '' && $targets['user'] === null) {
            return back()
                ->withErrors(['prompt' => 'No matching user found for that request.'])
                ->withInput();
        }
        if ($branchName !== '' && $targets['branch'] === null) {
            return back()
                ->withErrors(['prompt' => 'No matching branch found for that request.'])
                ->withInput();
        }

        $fallbackTarget = $targets['driver'] ?? $targets['customer'] ?? $targets['vehicle'];

        $reportType = strtolower(trim((string) ($intent['report_type'] ?? 'trips')));

        if ($request->input('export') === 'csv') {
            return $this->exportReportCsv(
                $organisationId,
                $user,
                $reportType,
                $fallbackTarget,
                $settings,
                $targets,
                $fromDate,
                $toDate,
                $clientPresent,
                $hasRegistration
            );
        }

        $result = $this->buildReport(
            $organisationId,
            $user,
            $reportType,
            $fallbackTarget,
            $settings,
            $targets,
            $fromDate,
            $toDate,
            $clientPresent,
            $hasRegistration,
            $this->resolvePerPage($request)
        );

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
        $candidates = [$entityType, 'customer', 'vehicle', 'driver', 'user', 'branch'];

        foreach ($candidates as $type) {
            if ($type === 'customer') {
                if (!$this->customerLinkingEnabled($organisationId, $settings)) {
                    continue;
                }

                $nameVariants = $this->customerNameVariants($name);
                $customer = DB::connection('sharpfleet')
                    ->table('customers')
                    ->where('organisation_id', $organisationId)
                    ->where('is_active', 1)
                    ->when($name !== '', function ($q) use ($nameVariants) {
                        $q->where(function ($sub) use ($nameVariants) {
                            foreach ($nameVariants as $variant) {
                                $sub->orWhere('name', 'like', '%' . $variant . '%');
                            }
                        });
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

            if ($type === 'user') {
                $user = DB::connection('sharpfleet')
                    ->table('users')
                    ->where('organisation_id', $organisationId)
                    ->when($name !== '', function ($q) use ($name) {
                        $q->where(function ($sub) use ($name) {
                            $sub->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $name . '%')
                                ->orWhere('first_name', 'like', '%' . $name . '%')
                                ->orWhere('last_name', 'like', '%' . $name . '%')
                                ->orWhere('email', 'like', '%' . $name . '%');
                        });
                    })
                    ->orderBy('first_name')
                    ->first();

                if ($user) {
                    return [
                        'type' => 'user',
                        'id' => (int) $user->id,
                        'label' => trim((string) $user->first_name . ' ' . (string) $user->last_name),
                    ];
                }
            }

            if ($type === 'branch') {
                if (!Schema::connection('sharpfleet')->hasTable('branches')) {
                    continue;
                }

                $branch = DB::connection('sharpfleet')
                    ->table('branches')
                    ->where('organisation_id', $organisationId)
                    ->when($name !== '', fn ($q) => $q->where('name', 'like', '%' . $name . '%'))
                    ->orderBy('name')
                    ->first();

                if ($branch) {
                    return [
                        'type' => 'branch',
                        'id' => (int) $branch->id,
                        'label' => (string) $branch->name,
                    ];
                }
            }
        }

        return null;
    }

    private function buildReport(
        int $organisationId,
        array $user,
        string $reportType,
        ?array $target,
        CompanySettingsService $settings,
        array $targets = [],
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null,
        ?bool $clientPresent = null,
        ?bool $hasRegistration = null,
        int $perPage = 10
    ): array {
        $reportType = $reportType !== '' ? $reportType : 'trips';

        if ($reportType === 'vehicles') {
            return $this->buildVehiclesReport(
                $organisationId,
                $user,
                $settings,
                $targets,
                $hasRegistration,
                $perPage
            );
        }

        if ($reportType === 'customers_with_trips') {
            return $this->buildCustomersWithTripsReport(
                $organisationId,
                $user,
                $settings,
                $fromDate,
                $toDate,
                $perPage
            );
        }

        if ($reportType === 'customers') {
            return $this->buildCustomersReport(
                $organisationId,
                $user,
                $settings,
                $perPage
            );
        }

        if ($reportType === 'users') {
            return $this->buildUsersReport(
                $organisationId,
                $user,
                $settings,
                $perPage
            );
        }

        if ($reportType === 'branches') {
            return $this->buildBranchesReport(
                $organisationId,
                $user,
                $settings,
                $perPage
            );
        }

        if ($reportType === 'faults') {
            return $this->buildFaultsReport(
                $organisationId,
                $user,
                $settings,
                $fromDate,
                $toDate,
                $perPage
            );
        }

        if ($reportType === 'bookings') {
            return $this->buildBookingsReport(
                $organisationId,
                $user,
                $settings,
                $fromDate,
                $toDate,
                $perPage
            );
        }

        return $this->buildTripsReport(
            $organisationId,
            $user,
            $target,
            $settings,
            $targets,
            $fromDate,
            $toDate,
            $clientPresent,
            $perPage
        );
    }

    private function buildTripsReport(
        int $organisationId,
        array $user,
        ?array $target,
        CompanySettingsService $settings,
        array $targets = [],
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null,
        ?bool $clientPresent = null,
        int $perPage = 10
    ): array {
        $reporting = $settings->all()['reporting'] ?? [];
        $includePrivateTrips = (bool) ($reporting['include_private_trips'] ?? true);

        if (!$fromDate && !$toDate) {
            [$startDate, $endDate] = $this->resolveDateRange(
                $settings->timezone(),
                (string) ($reporting['default_date_range'] ?? 'month_to_date')
            );
        } else {
            $startDate = $fromDate;
            $endDate = $toDate;
        }

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
            $query->where('trips.started_at', '>=', $startDate->copy()->timezone('UTC')->toDateTimeString());
        }
        if ($endDate) {
            $query->where('trips.started_at', '<=', $endDate->copy()->timezone('UTC')->toDateTimeString());
        }

        $driverTarget = $targets['driver'] ?? null;
        $customerTarget = $targets['customer'] ?? null;
        $vehicleTarget = $targets['vehicle'] ?? null;

        if (is_array($driverTarget) && isset($driverTarget['id'])) {
            $query->where('trips.user_id', (int) $driverTarget['id']);
        } elseif (is_array($target) && ($target['type'] ?? '') === 'driver') {
            $query->where('trips.user_id', (int) $target['id']);
        }

        if (is_array($vehicleTarget) && isset($vehicleTarget['id'])) {
            $query->where('trips.vehicle_id', (int) $vehicleTarget['id']);
        } elseif (is_array($target) && ($target['type'] ?? '') === 'vehicle') {
            $query->where('trips.vehicle_id', (int) $target['id']);
        }

        if (is_array($customerTarget) && isset($customerTarget['id'])) {
            $query->where(function ($sub) use ($customerTarget) {
                $sub->where('trips.customer_id', (int) $customerTarget['id'])
                    ->orWhere('trips.customer_name', (string) $customerTarget['label']);
            });
        } elseif (is_array($target) && ($target['type'] ?? '') === 'customer') {
            $query->where(function ($sub) use ($target) {
                $sub->where('trips.customer_id', (int) $target['id'])
                    ->orWhere('trips.customer_name', (string) $target['label']);
            });
        }

        if ($clientPresent !== null && Schema::connection('sharpfleet')->hasColumn('trips', 'client_present')) {
            $query->where('trips.client_present', $clientPresent ? 1 : 0);
        }

        $trips = $query
            ->orderByDesc('trips.started_at')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        $displayTrips = [];
        $totalTrips = $trips->total();
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

        $titleTarget = is_array($target) ? (string) ($target['label'] ?? 'All trips') : 'All trips';
        $subtitle = is_array($target)
            ? 'Based on ' . (string) ($target['type'] ?? 'trip') . ' matching "' . $titleTarget . '"'
            : 'All trips';

        return [
            'report_type' => 'trips',
            'title' => 'Trips Report for ' . $titleTarget,
            'subtitle' => $subtitle,
            'date_range' => $dateRangeLabel,
            'summary' => [
                ['label' => 'Total trips', 'value' => $totalTrips],
                ['label' => 'Total distance', 'value' => $distanceLabel],
                ['label' => 'Total drive time', 'value' => $this->formatDuration($totalSeconds)],
                ['label' => 'Vehicle used most', 'value' => $topVehicle ?? 'â€”'],
                ['label' => 'Purpose', 'value' => $purposeEnabled ? ($topPurpose ?: 'Not captured') : 'Not enabled'],
                ['label' => 'Top customer visited', 'value' => $customerLinkingEnabled ? ($topCustomer ?: 'None') : 'Not enabled'],
            ],
            'vehicles_used' => array_keys($vehicleCounts),
            'columns' => [
                ['key' => 'started_at', 'label' => 'Started'],
                ['key' => 'ended_at', 'label' => 'Ended'],
                ['key' => 'vehicle', 'label' => 'Vehicle'],
                ['key' => 'distance', 'label' => 'Distance'],
                ['key' => 'duration', 'label' => 'Duration'],
                ['key' => 'purpose', 'label' => 'Purpose'],
                ['key' => 'customer', 'label' => 'Customer'],
            ],
            'rows' => $displayTrips,
            'paginator' => $trips,
        ];
    }

    private function buildVehiclesReport(
        int $organisationId,
        array $user,
        CompanySettingsService $settings,
        array $targets,
        ?bool $hasRegistration,
        int $perPage
    ): array {
        $branchScope = $this->resolveBranchScope($organisationId, $user);
        [$branchScopeEnabled, $accessibleBranchIds] = $branchScope;

        $hasBranches = Schema::connection('sharpfleet')->hasTable('branches');
        $hasBranchId = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');
        $hasRegistrationExpiry = Schema::connection('sharpfleet')->hasColumn('vehicles', 'registration_expiry');
        $hasRoadRegistered = Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_road_registered');

        $query = DB::connection('sharpfleet')
            ->table('vehicles as v')
            ->leftJoin('branches as b', 'v.branch_id', '=', 'b.id')
            ->where('v.organisation_id', $organisationId)
            ->where('v.is_active', 1);

        if ($branchScopeEnabled && $hasBranchId && count($accessibleBranchIds) > 0) {
            $query->whereIn('v.branch_id', $accessibleBranchIds);
        }

        if ($hasRegistration === true) {
            $query->whereNotNull('v.registration_number')
                ->where('v.registration_number', '!=', '');
        }

        $rows = $query->select([
            'v.id',
            'v.name',
            'v.registration_number',
            $hasRegistrationExpiry ? 'v.registration_expiry' : DB::raw('NULL as registration_expiry'),
            $hasRoadRegistered ? 'v.is_road_registered' : DB::raw('NULL as is_road_registered'),
            $hasBranchId ? 'v.branch_id' : DB::raw('NULL as branch_id'),
            $hasBranches ? DB::raw('COALESCE(b.name, "") as branch_name') : DB::raw('NULL as branch_name'),
        ])
            ->orderBy('v.name')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        $dateFormat = $settings->dateFormat();
        $timezone = $settings->timezone();

        $mapped = $rows->map(function ($row) use ($dateFormat, $timezone, $hasRegistrationExpiry, $hasRoadRegistered) {
            $expiry = $hasRegistrationExpiry && !empty($row->registration_expiry)
                ? $this->formatDate($row->registration_expiry, $timezone, $dateFormat)
                : 'â€”';

            $road = $hasRoadRegistered
                ? ((int) ($row->is_road_registered ?? 0) === 1 ? 'Yes' : 'No')
                : 'â€”';

            return [
                'vehicle' => (string) ($row->name ?? 'â€”'),
                'registration_number' => (string) ($row->registration_number ?? 'â€”'),
                'registration_expiry' => $expiry,
                'road_registered' => $road,
                'branch' => (string) ($row->branch_name ?? 'â€”'),
            ];
        });

        return [
            'report_type' => 'vehicles',
            'title' => 'Vehicle Report',
            'subtitle' => $hasRegistration ? 'Vehicles with registration numbers.' : 'All active vehicles.',
            'summary' => [
                ['label' => 'Total vehicles', 'value' => $rows->total()],
            ],
            'columns' => [
                ['key' => 'vehicle', 'label' => 'Vehicle'],
                ['key' => 'registration_number', 'label' => 'Registration number'],
                ['key' => 'registration_expiry', 'label' => 'Registration expiry'],
                ['key' => 'road_registered', 'label' => 'Road registered'],
                ['key' => 'branch', 'label' => 'Branch'],
            ],
            'rows' => $mapped->all(),
            'paginator' => $rows,
        ];
    }

    private function buildCustomersReport(
        int $organisationId,
        array $user,
        CompanySettingsService $settings,
        int $perPage
    ): array {
        if (!$this->customerLinkingEnabled($organisationId, $settings)) {
            return [
                'report_type' => 'customers',
                'title' => 'Customers',
                'subtitle' => 'Customer capture is not enabled for this organisation.',
                'summary' => [],
                'columns' => [],
                'rows' => [],
                'paginator' => null,
            ];
        }

        $branchScope = $this->resolveBranchScope($organisationId, $user);
        [$branchScopeEnabled, $accessibleBranchIds] = $branchScope;
        $hasBranchId = Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id');
        $hasBranches = Schema::connection('sharpfleet')->hasTable('branches');

        $query = DB::connection('sharpfleet')
            ->table('customers as c')
            ->leftJoin('branches as b', 'c.branch_id', '=', 'b.id')
            ->where('c.organisation_id', $organisationId)
            ->where('c.is_active', 1);

        if ($branchScopeEnabled && $hasBranchId && count($accessibleBranchIds) > 0) {
            $query->whereIn('c.branch_id', $accessibleBranchIds);
        }

        $rows = $query->select([
            'c.id',
            'c.name',
            $hasBranchId ? 'c.branch_id' : DB::raw('NULL as branch_id'),
            $hasBranches ? DB::raw('COALESCE(b.name, "") as branch_name') : DB::raw('NULL as branch_name'),
            'c.created_at',
        ])
            ->orderBy('c.name')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        $dateFormat = $settings->dateFormat();
        $timezone = $settings->timezone();

        $mapped = $rows->map(function ($row) use ($dateFormat, $timezone) {
            return [
                'customer' => (string) ($row->name ?? 'â€”'),
                'branch' => (string) ($row->branch_name ?? 'â€”'),
                'created_at' => $this->formatDate($row->created_at, $timezone, $dateFormat),
            ];
        });

        return [
            'report_type' => 'customers',
            'title' => 'Customer Report',
            'subtitle' => 'All active customers.',
            'summary' => [
                ['label' => 'Total customers', 'value' => $rows->total()],
            ],
            'columns' => [
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'created_at', 'label' => 'Created'],
            ],
            'rows' => $mapped->all(),
            'paginator' => $rows,
        ];
    }

    private function buildCustomersWithTripsReport(
        int $organisationId,
        array $user,
        CompanySettingsService $settings,
        ?Carbon $fromDate,
        ?Carbon $toDate,
        int $perPage
    ): array {
        if (!$this->customerLinkingEnabled($organisationId, $settings)) {
            return [
                'report_type' => 'customers_with_trips',
                'title' => 'Customers & Trips',
                'subtitle' => 'Customer capture is not enabled for this organisation.',
                'summary' => [],
                'columns' => [],
                'rows' => [],
                'paginator' => null,
            ];
        }

        $branchScope = $this->resolveBranchScope($organisationId, $user);
        [$branchScopeEnabled, $accessibleBranchIds] = $branchScope;
        $hasBranchId = Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id');
        $hasVehicleBranch = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');

        $query = DB::connection('sharpfleet')
            ->table('customers as c')
            ->leftJoin('trips as t', function ($join) use ($organisationId, $fromDate, $toDate) {
                $join->on('t.customer_id', '=', 'c.id')
                    ->where('t.organisation_id', '=', $organisationId);
                if ($fromDate) {
                    $join->where('t.started_at', '>=', $fromDate->copy()->timezone('UTC')->toDateTimeString());
                }
                if ($toDate) {
                    $join->where('t.started_at', '<=', $toDate->copy()->timezone('UTC')->toDateTimeString());
                }
            })
            ->leftJoin('vehicles as v', 't.vehicle_id', '=', 'v.id')
            ->where('c.organisation_id', $organisationId)
            ->where('c.is_active', 1);

        if ($branchScopeEnabled && $hasBranchId && count($accessibleBranchIds) > 0) {
            $query->whereIn('c.branch_id', $accessibleBranchIds);
        }

        if ($branchScopeEnabled && $hasVehicleBranch && count($accessibleBranchIds) > 0) {
            $query->where(function ($sub) use ($accessibleBranchIds) {
                $sub->whereNull('t.vehicle_id')
                    ->orWhereIn('v.branch_id', $accessibleBranchIds);
            });
        }

        $rows = $query->select([
            'c.id',
            'c.name',
            DB::raw('COUNT(t.id) as trip_count'),
            DB::raw('MAX(t.started_at) as last_trip_at'),
        ])
            ->groupBy('c.id', 'c.name')
            ->orderBy('c.name')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        $dateFormat = $settings->dateFormat();
        $timezone = $settings->timezone();

        $mapped = $rows->map(function ($row) use ($dateFormat, $timezone) {
            $lastTrip = $row->last_trip_at
                ? $this->formatDateTime($row->last_trip_at, $timezone, $dateFormat, 'H:i')
                : 'â€”';

            return [
                'customer' => (string) ($row->name ?? 'â€”'),
                'trip_count' => (int) ($row->trip_count ?? 0),
                'last_trip' => $lastTrip,
            ];
        });

        return [
            'report_type' => 'customers_with_trips',
            'title' => 'Customers & Trips',
            'subtitle' => 'Customers with trips logged against them.',
            'summary' => [
                ['label' => 'Total customers', 'value' => $rows->total()],
            ],
            'columns' => [
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'trip_count', 'label' => 'Trips'],
                ['key' => 'last_trip', 'label' => 'Last trip'],
            ],
            'rows' => $mapped->all(),
            'paginator' => $rows,
        ];
    }

    private function buildUsersReport(
        int $organisationId,
        array $user,
        CompanySettingsService $settings,
        int $perPage
    ): array {
        $hasBranchId = Schema::connection('sharpfleet')->hasColumn('users', 'branch_id');
        $branchScope = $this->resolveBranchScope($organisationId, $user);
        [$branchScopeEnabled, $accessibleBranchIds] = $branchScope;

        $query = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId);

        if ($branchScopeEnabled && $hasBranchId && count($accessibleBranchIds) > 0) {
            $query->whereIn('branch_id', $accessibleBranchIds);
        }

        $rows = $query->select([
            'id',
            'first_name',
            'last_name',
            'email',
            'role',
            'is_driver',
            'is_active',
            'archived_at',
        ])
            ->orderBy('first_name')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        $mapped = $rows->map(function ($row) {
            $status = (int) ($row->is_active ?? 0) === 1 ? 'Active' : 'Inactive';
            if (!empty($row->archived_at)) {
                $status = 'Archived';
            }

            return [
                'user' => trim((string) ($row->first_name ?? '') . ' ' . (string) ($row->last_name ?? '')),
                'email' => (string) ($row->email ?? 'â€”'),
                'role' => (string) ($row->role ?? 'â€”'),
                'driver' => (int) ($row->is_driver ?? 0) === 1 ? 'Yes' : 'No',
                'status' => $status,
            ];
        });

        return [
            'report_type' => 'users',
            'title' => 'User Report',
            'subtitle' => 'All users for this organisation.',
            'summary' => [
                ['label' => 'Total users', 'value' => $rows->total()],
            ],
            'columns' => [
                ['key' => 'user', 'label' => 'User'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'role', 'label' => 'Role'],
                ['key' => 'driver', 'label' => 'Driver'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $mapped->all(),
            'paginator' => $rows,
        ];
    }

    private function buildBranchesReport(
        int $organisationId,
        array $user,
        CompanySettingsService $settings,
        int $perPage
    ): array {
        if (!Schema::connection('sharpfleet')->hasTable('branches')) {
            return [
                'report_type' => 'branches',
                'title' => 'Branches',
                'subtitle' => 'Branches are not enabled for this organisation.',
                'summary' => [],
                'columns' => [],
                'rows' => [],
                'paginator' => null,
            ];
        }

        $branchScope = $this->resolveBranchScope($organisationId, $user);
        [$branchScopeEnabled, $accessibleBranchIds] = $branchScope;

        $query = DB::connection('sharpfleet')
            ->table('branches as b')
            ->leftJoin('vehicles as v', 'v.branch_id', '=', 'b.id')
            ->where('b.organisation_id', $organisationId);

        if ($branchScopeEnabled && count($accessibleBranchIds) > 0) {
            $query->whereIn('b.id', $accessibleBranchIds);
        }

        $rows = $query->select([
            'b.id',
            'b.name',
            Schema::connection('sharpfleet')->hasColumn('branches', 'timezone')
                ? 'b.timezone'
                : DB::raw('NULL as timezone'),
            DB::raw('COUNT(v.id) as vehicle_count'),
        ])
            ->groupBy('b.id', 'b.name', 'b.timezone')
            ->orderBy('b.name')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        $mapped = $rows->map(function ($row) {
            return [
                'branch' => (string) ($row->name ?? 'â€”'),
                'timezone' => (string) ($row->timezone ?? 'â€”'),
                'vehicles' => (int) ($row->vehicle_count ?? 0),
            ];
        });

        return [
            'report_type' => 'branches',
            'title' => 'Branch Report',
            'subtitle' => 'Branch summary and vehicle counts.',
            'summary' => [
                ['label' => 'Total branches', 'value' => $rows->total()],
            ],
            'columns' => [
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'timezone', 'label' => 'Timezone'],
                ['key' => 'vehicles', 'label' => 'Vehicles'],
            ],
            'rows' => $mapped->all(),
            'paginator' => $rows,
        ];
    }

    private function buildFaultsReport(
        int $organisationId,
        array $user,
        CompanySettingsService $settings,
        ?Carbon $fromDate,
        ?Carbon $toDate,
        int $perPage
    ): array {
        if (!Schema::connection('sharpfleet')->hasTable('faults')) {
            return [
                'report_type' => 'faults',
                'title' => 'Faults',
                'subtitle' => 'Fault reporting is not available.',
                'summary' => [],
                'columns' => [],
                'rows' => [],
                'paginator' => null,
            ];
        }

        $branchScope = $this->resolveBranchScope($organisationId, $user);
        [$branchScopeEnabled, $accessibleBranchIds] = $branchScope;
        $hasVehicleBranch = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');

        $query = DB::connection('sharpfleet')
            ->table('faults as f')
            ->leftJoin('vehicles as v', 'f.vehicle_id', '=', 'v.id')
            ->leftJoin('users as u', 'f.user_id', '=', 'u.id')
            ->where('f.organisation_id', $organisationId);

        if ($branchScopeEnabled && $hasVehicleBranch && count($accessibleBranchIds) > 0) {
            $query->whereIn('v.branch_id', $accessibleBranchIds);
        }

        if ($fromDate) {
            $query->where('f.created_at', '>=', $fromDate->copy()->timezone('UTC')->toDateTimeString());
        }
        if ($toDate) {
            $query->where('f.created_at', '<=', $toDate->copy()->timezone('UTC')->toDateTimeString());
        }

        $rows = $query->select([
            'f.id',
            'f.status',
            'f.severity',
            Schema::connection('sharpfleet')->hasColumn('faults', 'report_type')
                ? 'f.report_type'
                : DB::raw('NULL as report_type'),
            'f.created_at',
            'v.name as vehicle_name',
            'v.registration_number as vehicle_registration_number',
            DB::raw("CONCAT(u.first_name, ' ', u.last_name) as driver_name"),
        ])
            ->orderByDesc('f.created_at')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        $dateFormat = $settings->dateFormat();
        $timezone = $settings->timezone();

        $mapped = $rows->map(function ($row) use ($dateFormat, $timezone) {
            return [
                'created_at' => $this->formatDateTime($row->created_at, $timezone, $dateFormat, 'H:i'),
                'vehicle' => trim((string) ($row->vehicle_name ?? '')),
                'registration_number' => (string) ($row->vehicle_registration_number ?? 'â€”'),
                'driver' => (string) ($row->driver_name ?? 'â€”'),
                'severity' => (string) ($row->severity ?? 'â€”'),
                'status' => (string) ($row->status ?? 'â€”'),
                'type' => (string) ($row->report_type ?? 'â€”'),
            ];
        });

        return [
            'report_type' => 'faults',
            'title' => 'Faults Report',
            'subtitle' => 'Reported vehicle issues and accidents.',
            'summary' => [
                ['label' => 'Total faults', 'value' => $rows->total()],
            ],
            'columns' => [
                ['key' => 'created_at', 'label' => 'Reported'],
                ['key' => 'vehicle', 'label' => 'Vehicle'],
                ['key' => 'registration_number', 'label' => 'Registration number'],
                ['key' => 'driver', 'label' => 'Driver'],
                ['key' => 'severity', 'label' => 'Severity'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'type', 'label' => 'Type'],
            ],
            'rows' => $mapped->all(),
            'paginator' => $rows,
        ];
    }

    private function buildBookingsReport(
        int $organisationId,
        array $user,
        CompanySettingsService $settings,
        ?Carbon $fromDate,
        ?Carbon $toDate,
        int $perPage
    ): array {
        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            return [
                'report_type' => 'bookings',
                'title' => 'Bookings',
                'subtitle' => 'Bookings are not available for this organisation.',
                'summary' => [],
                'columns' => [],
                'rows' => [],
                'paginator' => null,
            ];
        }

        $branchScope = $this->resolveBranchScope($organisationId, $user);
        [$branchScopeEnabled, $accessibleBranchIds] = $branchScope;
        $hasBookingBranch = Schema::connection('sharpfleet')->hasColumn('bookings', 'branch_id');
        $hasVehicleBranch = Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');
        $hasCustomers = Schema::connection('sharpfleet')->hasTable('customers');

        $query = DB::connection('sharpfleet')
            ->table('bookings')
            ->leftJoin('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id');

        if ($hasCustomers) {
            $query->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id');
        }

        $query->where('bookings.organisation_id', $organisationId);

        if ($branchScopeEnabled && count($accessibleBranchIds) > 0) {
            if ($hasBookingBranch) {
                $query->whereIn('bookings.branch_id', $accessibleBranchIds);
            } elseif ($hasVehicleBranch) {
                $query->whereIn('vehicles.branch_id', $accessibleBranchIds);
            }
        }

        if ($fromDate) {
            $query->where('bookings.planned_start', '>=', $fromDate->copy()->timezone('UTC')->toDateTimeString());
        }
        if ($toDate) {
            $query->where('bookings.planned_start', '<=', $toDate->copy()->timezone('UTC')->toDateTimeString());
        }

        $rows = $query->select([
            'bookings.id',
            'bookings.planned_start',
            'bookings.planned_end',
            'bookings.created_at',
            'vehicles.name as vehicle_name',
            'vehicles.registration_number',
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name"),
            $hasCustomers
                ? DB::raw('COALESCE(customers.name, bookings.customer_name) as customer_name_display')
                : DB::raw('bookings.customer_name as customer_name_display'),
        ])
            ->orderByDesc('bookings.planned_start')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);

        $dateFormat = $settings->dateFormat();
        $timezone = $settings->timezone();

        $mapped = $rows->map(function ($row) use ($dateFormat, $timezone) {
            return [
                'planned_start' => $this->formatDateTime($row->planned_start, $timezone, $dateFormat, 'H:i'),
                'planned_end' => $this->formatDateTime($row->planned_end, $timezone, $dateFormat, 'H:i'),
                'vehicle' => (string) ($row->vehicle_name ?? 'â€”'),
                'registration_number' => (string) ($row->registration_number ?? 'â€”'),
                'driver' => (string) ($row->driver_name ?? 'â€”'),
                'customer' => (string) ($row->customer_name_display ?? 'â€”'),
            ];
        });

        return [
            'report_type' => 'bookings',
            'title' => 'Bookings Report',
            'subtitle' => 'Upcoming and historical bookings.',
            'summary' => [
                ['label' => 'Total bookings', 'value' => $rows->total()],
            ],
            'columns' => [
                ['key' => 'planned_start', 'label' => 'Planned start'],
                ['key' => 'planned_end', 'label' => 'Planned end'],
                ['key' => 'vehicle', 'label' => 'Vehicle'],
                ['key' => 'registration_number', 'label' => 'Registration number'],
                ['key' => 'driver', 'label' => 'Driver'],
                ['key' => 'customer', 'label' => 'Customer'],
            ],
            'rows' => $mapped->all(),
            'paginator' => $rows,
        ];
    }

    private function exportReportCsv(
        int $organisationId,
        array $user,
        string $reportType,
        ?array $target,
        CompanySettingsService $settings,
        array $targets,
        ?Carbon $fromDate,
        ?Carbon $toDate,
        ?bool $clientPresent,
        ?bool $hasRegistration
    ): Response|StreamedResponse {
        $result = $this->buildReport(
            $organisationId,
            $user,
            $reportType,
            $target,
            $settings,
            $targets,
            $fromDate,
            $toDate,
            $clientPresent,
            $hasRegistration,
            10000
        );

        $columns = $result['columns'] ?? [];
        $rows = $result['rows'] ?? [];
        $filename = strtolower(str_replace(' ', '-', $result['title'] ?? 'report')) . '.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_map(fn ($c) => $c['label'] ?? '', $columns));
            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $col) {
                    $key = $col['key'] ?? '';
                    $line[] = $key !== '' ? ($row[$key] ?? '') : '';
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resolveBranchScope(int $organisationId, array $user): array
    {
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

        return [$branchScopeEnabled, $accessibleBranchIds];
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 10);
        $allowed = [10, 25, 50, 100];
        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }

    private function parseDateRange(?string $from, ?string $to, string $timezone): array
    {
        $fromDate = null;
        $toDate = null;

        try {
            if (is_string($from) && trim($from) !== '') {
                $fromDate = Carbon::parse(trim($from), $timezone)->startOfDay();
            }
        } catch (\Throwable $e) {
            $fromDate = null;
        }

        try {
            if (is_string($to) && trim($to) !== '') {
                $toDate = Carbon::parse(trim($to), $timezone)->endOfDay();
            }
        } catch (\Throwable $e) {
            $toDate = null;
        }

        $appTz = (string) (config('app.timezone') ?: 'UTC');
        return [
            $fromDate ? $fromDate->copy()->setTimezone($appTz) : null,
            $toDate ? $toDate->copy()->setTimezone($appTz) : null,
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

    private function formatDateTime(string|CarbonInterface|null $value, string $timezone, string $dateFormat, string $timeFormat): string
    {
        if (!$value) {
            return "-";
        }

        try {
            if ($value instanceof CarbonInterface) {
                return $value->copy()->timezone($timezone)->format($dateFormat . ' ' . $timeFormat);
            }

            return Carbon::parse($value, 'UTC')->timezone($timezone)->format($dateFormat . ' ' . $timeFormat);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function formatDate(string|CarbonInterface|null $value, string $timezone, string $dateFormat): string
    {
        if (!$value) {
            return "-";
        }

        try {
            if ($value instanceof CarbonInterface) {
                return $value->copy()->timezone($timezone)->format($dateFormat);
            }

            return Carbon::parse($value, 'UTC')->timezone($timezone)->format($dateFormat);
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

    private function customerNameVariants(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return [];
        }

        $variants = [$name];
        $normalized = preg_replace('/\s+/', ' ', $name);
        if (is_string($normalized) && $normalized !== $name) {
            $variants[] = $normalized;
        }

        if (str_contains($name, ' and ')) {
            $variants[] = str_replace(' and ', ' & ', $name);
        }
        if (str_contains($name, ' & ')) {
            $variants[] = str_replace(' & ', ' and ', $name);
        }

        return array_values(array_unique(array_filter($variants)));
    }
}


