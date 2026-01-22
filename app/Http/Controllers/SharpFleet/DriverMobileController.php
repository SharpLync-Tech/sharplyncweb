<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\Roles;
use Illuminate\Support\Str;

class DriverMobileController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        $settingsService = new CompanySettingsService($user['organisation_id']);
        $settings = $settingsService->all();

        $allowPrivateTrips       = $settingsService->allowPrivateTrips();
        $faultsEnabled           = $settingsService->faultsEnabled();
        $allowFaultsDuringTrip   = $settingsService->allowFaultsDuringTrip();
        $companyTimezone         = $settingsService->timezone();

        $odometerRequired        = $settingsService->odometerRequired();
        $odometerAllowOverride   = $settingsService->odometerAllowOverride();
        $manualTripTimesRequired = $settingsService->requireManualStartEndTimes();

        $safetyCheckEnabled      = $settingsService->safetyCheckEnabled();
        $safetyCheckItems        = $settingsService->safetyCheckItems();

        $branchesService = new BranchService();

        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();

        $accessibleBranchIds = $branchAccessEnabled
            ? $branchesService->getAccessibleBranchIdsForUser(
                (int) $user['organisation_id'],
                (int) $user['id']
            )
            : [];

        if ($branchAccessEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $user['organisation_id'])
            ->where('is_active', 1)
            ->when(
                $branchAccessEnabled,
                fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
            )
            ->when(
                Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
                && Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id'),
                fn ($q) => $q->where(function ($qq) use ($user) {
                    $qq->whereNull('assignment_type')
                        ->orWhere('assignment_type', 'none')
                        ->orWhere(function ($qq2) use ($user) {
                            $qq2->where('assignment_type', 'permanent')
                                ->where('assigned_driver_id', (int) $user['id']);
                        });
                })
            )
            ->when(
                Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service'),
                fn ($q) => $q->where('is_in_service', 1)
            )
            ->orderBy('name')
            ->get();

        $availableVehicleCount = $vehicles->count();
        if ($availableVehicleCount > 0) {
            $vehicleIds = $vehicles->pluck('id')->map(fn ($id) => (int) $id)->all();
            $blockedVehicleIds = DB::connection('sharpfleet')
                ->table('trips')
                ->where('organisation_id', $user['organisation_id'])
                ->whereNotNull('started_at')
                ->whereNull('ended_at')
                ->whereNotNull('vehicle_id')
                ->whereIn('vehicle_id', $vehicleIds)
                ->pluck('vehicle_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (Schema::connection('sharpfleet')->hasTable('bookings')) {
                $nowUtc = \Carbon\Carbon::now('UTC');
                $bookingVehicleIds = DB::connection('sharpfleet')
                    ->table('bookings')
                    ->where('organisation_id', $user['organisation_id'])
                    ->whereIn('vehicle_id', $vehicleIds)
                    ->where('status', 'planned')
                    ->where('planned_start', '<=', $nowUtc->toDateTimeString())
                    ->where('planned_end', '>=', $nowUtc->toDateTimeString())
                    ->where('user_id', '!=', $user['id'])
                    ->pluck('vehicle_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $blockedVehicleIds = array_values(array_unique(array_merge($blockedVehicleIds, $bookingVehicleIds)));
            }

            $availableVehicleCount = count(array_diff($vehicleIds, $blockedVehicleIds));
        }

        $customers = collect();
        if (($settings['customer']['enabled'] ?? false) && Schema::connection('sharpfleet')->hasTable('customers')) {
            $customers = DB::connection('sharpfleet')
                ->table('customers')
                ->where('organisation_id', $user['organisation_id'])
                ->where('is_active', 1)
                ->orderBy('name')
                ->limit(500)
                ->get();
        }

        $lastTrips = DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->select('trips.vehicle_id', 'trips.end_km')
            ->where('trips.organisation_id', $user['organisation_id'])
            ->when(
                $branchAccessEnabled,
                fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
            )
            ->whereNotNull('ended_at')
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->get()
            ->unique('vehicle_id')
            ->keyBy('vehicle_id');

        $activeTrip = DB::connection('sharpfleet')
            ->table('trips')
            ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->select(
                'trips.*',
                'vehicles.name as vehicle_name',
                'vehicles.registration_number',
                'vehicles.tracking_mode',
                'vehicles.branch_id as vehicle_branch_id'
            )
            ->where('trips.user_id', $user['id'])
            ->where('trips.organisation_id', $user['organisation_id'])
            ->when(
                $branchAccessEnabled,
                fn ($q) => $q->where(function ($sub) use ($accessibleBranchIds) {
                    $sub->whereNull('trips.vehicle_id')
                        ->orWhereIn('vehicles.branch_id', $accessibleBranchIds);
                })
            )
            ->whereNotNull('trips.started_at')
            ->whereNull('trips.ended_at')
            ->first();

        $organisationName = (string) (DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', (int) $user['organisation_id'])
            ->value('name') ?? '');

        return view('sharpfleet.mobile.dashboard', compact(
            'user',
            'settingsService',
            'settings',
            'vehicles',
            'customers',
            'lastTrips',
            'activeTrip',
            'organisationName',
            'allowPrivateTrips',
            'faultsEnabled',
            'allowFaultsDuringTrip',
            'companyTimezone',
            'odometerRequired',
            'odometerAllowOverride',
            'manualTripTimesRequired',
            'safetyCheckEnabled',
            'safetyCheckItems',
            'availableVehicleCount'
        ));
    }

    public function history(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.history');
    }

    public function bookings(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];
        $settingsService = new CompanySettingsService($organisationId);
        $companyTimezone = $settingsService->timezone();
        $settings = $settingsService->all();

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $branches = $branchesEnabled ? $branchesService->getBranchesForUser($organisationId, (int) $user['id']) : collect();

        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        $accessibleBranchIds = $branchAccessEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) $user['id'])
            : [];

        if ($branchAccessEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $customersTableExists = Schema::connection('sharpfleet')->hasTable('customers');
        $customerEnabled = (bool) ($settings['customer']['enabled'] ?? false);
        $customerJoinEnabled = $customersTableExists && $customerEnabled;
        $customers = collect();

        if ($customersTableExists && $customerEnabled) {
            $customers = DB::connection('sharpfleet')
                ->table('customers')
                ->where('organisation_id', $organisationId)
                ->where('is_active', 1)
                ->when(
                    $branchAccessEnabled
                        && Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id')
                        && count($accessibleBranchIds) > 0,
                    fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
                )
                ->orderBy('name')
                ->limit(500)
                ->get();
        }

        $bookingsTableExists = Schema::connection('sharpfleet')->hasTable('bookings');
        $bookingsMine = collect();
        $bookingsOther = collect();

        $nowLocal = \Carbon\Carbon::now($companyTimezone);
        $dayStartLocal = $nowLocal->copy()->startOfDay();
        $dayEndLocal = $nowLocal->copy()->endOfDay();
        $weekStartLocal = $nowLocal->copy()->startOfWeek();
        $weekEndLocal = $nowLocal->copy()->endOfWeek();
        $monthStartLocal = $nowLocal->copy()->startOfMonth();
        $monthEndLocal = $nowLocal->copy()->endOfMonth();

        // Fetch a superset that covers the current month plus week edges.
        $fetchStartLocal = $monthStartLocal->copy()->startOfWeek();
        $fetchEndLocal = $monthEndLocal->copy()->endOfWeek();

        if ($bookingsTableExists) {
            $rangeStartUtc = $fetchStartLocal->copy()->timezone('UTC');
            $rangeEndUtc = $fetchEndLocal->copy()->timezone('UTC');

            $query = DB::connection('sharpfleet')
                ->table('bookings')
                ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
                ->when(
                    $customerJoinEnabled,
                    fn ($q) => $q->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
                )
                ->select(
                    'bookings.id',
                    'bookings.user_id',
                    'bookings.planned_start',
                    'bookings.planned_end',
                    'bookings.status',
                    'bookings.timezone',
                    'vehicles.name as vehicle_name',
                    'vehicles.registration_number',
                    $customerJoinEnabled
                        ? DB::raw('COALESCE(customers.name, bookings.customer_name) as customer_name_display')
                        : DB::raw('bookings.customer_name as customer_name_display')
                )
                ->where('bookings.organisation_id', $organisationId)
                ->where('bookings.status', 'planned')
                ->where('bookings.planned_start', '<=', $rangeEndUtc->toDateTimeString())
                ->where('bookings.planned_end', '>=', $rangeStartUtc->toDateTimeString())
                ->when(
                    $branchAccessEnabled && $branchesService->bookingsHaveBranchSupport(),
                    fn ($q) => $q->whereIn('bookings.branch_id', $accessibleBranchIds)
                )
                ->when(
                    $branchAccessEnabled && !$branchesService->bookingsHaveBranchSupport() && $branchesService->vehiclesHaveBranchSupport(),
                    fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
                )
                ->orderBy('bookings.planned_start');

            $bookings = $query->get();

            $bookingsMine = $bookings->filter(function ($b) use ($user) {
                return (int) $b->user_id === (int) $user['id'];
            })->values();

            $bookingsOther = $bookings->filter(function ($b) use ($user) {
                return (int) $b->user_id !== (int) $user['id'];
            })->values();
        }

        $today = $nowLocal->format('Y-m-d');

        return view('sharpfleet.mobile.bookings', [
            'bookingsTableExists' => $bookingsTableExists,
            'bookingsMine' => $bookingsMine,
            'bookingsOther' => $bookingsOther,
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'customersTableExists' => $customersTableExists && $customerEnabled,
            'customers' => $customers,
            'companyTimezone' => $companyTimezone,
            'dayStartLocal' => $dayStartLocal,
            'dayEndLocal' => $dayEndLocal,
            'weekStartLocal' => $weekStartLocal,
            'weekEndLocal' => $weekEndLocal,
            'monthStartLocal' => $monthStartLocal,
            'monthEndLocal' => $monthEndLocal,
            'nowLocal' => $nowLocal,
            'today' => $today,
        ]);
    }

    public function more(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.more');
    }

    public function support(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        $organisationName = (string) (DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', (int) $user['organisation_id'])
            ->value('name') ?? '');

        return view('sharpfleet.mobile.support', [
            'user' => $user,
            'organisationName' => $organisationName,
        ]);
    }

    public function supportSend(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'string', 'max:40'],
            'usage_mode' => ['nullable', 'string', 'max:40'],
            'client_timezone' => ['nullable', 'string', 'max:120'],
            'logs' => ['nullable', 'string', 'max:20000'],
        ]);

        $name = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        $email = trim((string) ($user['email'] ?? ''));
        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $settingsService = new CompanySettingsService($organisationId);
        $companyTimezone = $settingsService->timezone();
        $organisationName = (string) (DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->value('name') ?? '');

        $admin = DB::connection('sharpfleet')
            ->table('users')
            ->select('first_name', 'last_name', 'email', 'role')
            ->where('organisation_id', $organisationId)
            ->whereIn('role', ['company_admin', 'admin'])
            ->orderByRaw("CASE WHEN role = 'company_admin' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();

        $adminName = '';
        $adminEmail = '';
        if ($admin) {
            $adminName = trim((string) (($admin->first_name ?? '') . ' ' . ($admin->last_name ?? '')));
            $adminEmail = trim((string) ($admin->email ?? ''));
        }

        $bodyLines = [
            'SharpFleet Mobile Support Request',
            '----------------------------------',
            'Name: ' . ($name !== '' ? $name : 'Unknown'),
            'Email: ' . ($email !== '' ? $email : 'Unknown'),
            'Organisation ID: ' . ($organisationId ?: 'Unknown'),
            'Organisation Name: ' . ($organisationName !== '' ? $organisationName : 'Unknown'),
            'Company Admin: ' . ($adminName !== '' ? $adminName : 'Unknown'),
            'Company Admin Email: ' . ($adminEmail !== '' ? $adminEmail : 'Unknown'),
            'Platform: ' . ($validated['platform'] ?? 'Unknown'),
            'Usage mode: ' . ($validated['usage_mode'] ?? 'Unknown'),
            'Client Timezone: ' . ($validated['client_timezone'] ?? 'Unknown'),
            'Company Timezone: ' . ($companyTimezone !== '' ? $companyTimezone : 'Unknown'),
            'Submitted: ' . now()->toDateTimeString(),
            '',
            'Message:',
            $validated['message'],
            '',
        ];

        if (!empty($validated['logs'])) {
            $bodyLines[] = 'Device Logs (warnings/errors, last 3 days / 100 entries):';
            $bodyLines[] = $validated['logs'];
        }

        $body = implode("\n", $bodyLines);

        try {
            Mail::raw($body, function ($message) use ($email, $name) {
                $message->to('info@sharplync.com.au')
                    ->subject('SharpFleet Mobile Support Request');

                if ($email !== '') {
                    $message->replyTo($email, $name !== '' ? $name : null);
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not send your support request. Please try again.');
        }

        return back()->with('success', 'Support request sent. We will get back to you shortly.');
    }

    public function fuelStore(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $settingsService = new CompanySettingsService($organisationId);
        $settings = $settingsService->all();

        if (!($settings['vehicles']['fuel_receipts_enabled'] ?? false)) {
            return response()->json(['message' => 'Fuel receipts are disabled.'], 403);
        }

        $recipient = trim((string) ($settings['vehicles']['fuel_receipts_email'] ?? ''));
        if ($recipient === '') {
            return response()->json(['message' => 'Fuel receipt email not configured.'], 422);
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'min:1'],
            'odometer_reading' => ['nullable', 'integer', 'min:0'],
            'receipt' => ['required', 'image', 'max:5120'],
        ]);

        $vehicleId = (int) $validated['vehicle_id'];
        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('id', 'name', 'registration_number', 'organisation_id')
            ->where('id', $vehicleId)
            ->where('organisation_id', $organisationId)
            ->first();

        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found.'], 404);
        }

        $file = $request->file('receipt');
        if (!$file) {
            return response()->json(['message' => 'Receipt upload missing.'], 422);
        }

        $filename = now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            'sharpfleet/fuel-receipts/' . $organisationId,
            $filename
        );

        $entryId = DB::connection('sharpfleet')
            ->table('sf_fuel_entries')
            ->insertGetId([
                'organisation_id' => $organisationId,
                'vehicle_id' => $vehicleId,
                'driver_id' => (int) ($user['id'] ?? 0),
                'trip_id' => null,
                'odometer_reading' => isset($validated['odometer_reading']) ? (int) $validated['odometer_reading'] : 0,
                'receipt_path' => $path,
                'receipt_original_name' => $file->getClientOriginalName(),
                'receipt_mime' => $file->getClientMimeType(),
                'receipt_size_bytes' => $file->getSize(),
                'notes' => null,
                'emailed_to' => null,
                'emailed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $organisationName = (string) (DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->value('name') ?? '');

        $driverName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        $driverEmail = trim((string) ($user['email'] ?? ''));
        $vehicleLabel = trim((string) (($vehicle->name ?? '') . ' (' . ($vehicle->registration_number ?? '') . ')'));
        $odometerReading = isset($validated['odometer_reading'])
            ? (int) $validated['odometer_reading']
            : 0;

        $mailData = [
            'organisationName' => $organisationName !== '' ? $organisationName : 'Organisation',
            'vehicleLabel' => $vehicleLabel !== '' ? $vehicleLabel : 'Vehicle',
            'driverName' => $driverName !== '' ? $driverName : 'Driver',
            'driverEmail' => $driverEmail !== '' ? $driverEmail : 'Unknown',
            'odometerReading' => $odometerReading,
            'submittedAt' => now()->toDateTimeString(),
        ];

        try {
            $fileContents = Storage::get($path);
            \Mail::send('emails.sharpfleet.fuel-receipt', $mailData, function ($message) use ($recipient, $file, $fileContents) {
                $message->to($recipient)
                    ->subject('SharpFleet Fuel Receipt');

                $message->attachData($fileContents, $file->getClientOriginalName(), [
                    'mime' => $file->getClientMimeType(),
                ]);
            });
        } catch (\Throwable $e) {
            \Log::error('Fuel receipt email failed', [
                'organisation_id' => $organisationId,
                'vehicle_id' => $vehicleId,
                'driver_id' => (int) ($user['id'] ?? 0),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Could not email receipt: ' . $e->getMessage(),
            ], 500);
        }

        DB::connection('sharpfleet')
            ->table('sf_fuel_entries')
            ->where('id', $entryId)
            ->update([
                'emailed_to' => $recipient,
                'emailed_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    public function help(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.help');
    }

    public function about(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.about');
    }
}
