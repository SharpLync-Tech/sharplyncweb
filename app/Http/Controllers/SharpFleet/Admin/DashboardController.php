<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BillingDisplayService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\EntitlementService;
use App\Services\SharpFleet\VehicleReminderService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::isAdminPortal($user)) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $settingsService = new CompanySettingsService($organisationId);

        $driversCount = (int) DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where(function ($q) {
                $q
                    ->where(function ($qq) {
                        $qq
                            ->where('role', 'driver')
                            ->where(function ($q2) {
                                $q2->whereNull('is_driver')->orWhere('is_driver', 1);
                            });
                    })
                    ->orWhere(function ($qq) {
                        $qq
                            ->where('role', 'admin')
                            ->where('is_driver', 1);
                    });
            })
            ->count();

        $vehiclesCount = (int) DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->count();

        $hasVehicleAssignmentSupport = Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
            && Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id');

        $permanentAssignedVehiclesCount = 0;
        if ($hasVehicleAssignmentSupport) {
            $permanentAssignedVehiclesCount = (int) DB::connection('sharpfleet')
                ->table('vehicles')
                ->where('organisation_id', $organisationId)
                ->where('is_active', 1)
                ->where('assignment_type', 'permanent')
                ->count();
        }

        $hasOutOfServiceSupport = Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service');
        $outOfServiceVehiclesCount = 0;
        if ($hasOutOfServiceSupport) {
            $outOfServiceVehiclesCount = (int) DB::connection('sharpfleet')
                ->table('vehicles')
                ->where('organisation_id', $organisationId)
                ->where('is_active', 1)
                ->where('is_in_service', 0)
                ->count();
        }

        $activeTripsCount = (int) DB::connection('sharpfleet')
            ->table('trips')
            ->where('organisation_id', $organisationId)
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->count();

        $trialDaysRemaining = null;
        $billingSummary = [];
        try {
            $entitlements = new EntitlementService($user);
            $trialDaysRemaining = $entitlements->trialDaysRemaining();
        } catch (\Throwable $e) {
            $trialDaysRemaining = null;
        }

        try {
            $billingSummary = (new BillingDisplayService())
                ->getOrganisationBillingSummary($organisationId);
        } catch (\Throwable $e) {
            $billingSummary = [];
        }

        $vehicleReminders = null;
        try {
            $reminderService = new VehicleReminderService();
            $digest = $reminderService->buildDigest(
                $organisationId,
                $settingsService->reminderRegistrationDays(),
                $settingsService->reminderServiceDays(),
                $settingsService->reminderServiceReadingThreshold(),
                $settingsService->timezone()
            );

            $vehicleReminders = [
                'rego_enabled' => $settingsService->vehicleRegistrationTrackingEnabled(),
                'service_enabled' => $settingsService->vehicleServicingTrackingEnabled(),
                'rego_overdue' => count($digest['registration']['overdue'] ?? []),
                'rego_due_soon' => count($digest['registration']['due_soon'] ?? []),
                'service_date_overdue' => count($digest['serviceDate']['overdue'] ?? []),
                'service_date_due_soon' => count($digest['serviceDate']['due_soon'] ?? []),
                'service_reading_overdue' => count($digest['serviceReading']['overdue'] ?? []),
                'service_reading_due_soon' => count($digest['serviceReading']['due_soon'] ?? []),
            ];
        } catch (\Throwable $e) {
            $vehicleReminders = [
                'rego_enabled' => false,
                'service_enabled' => false,
                'rego_overdue' => 0,
                'rego_due_soon' => 0,
                'service_date_overdue' => 0,
                'service_date_due_soon' => 0,
                'service_reading_overdue' => 0,
                'service_reading_due_soon' => 0,
            ];
        }

        return view('sharpfleet.admin.dashboard', [
            'driversCount' => $driversCount,
            'vehiclesCount' => $vehiclesCount,
            'hasVehicleAssignmentSupport' => $hasVehicleAssignmentSupport,
            'permanentAssignedVehiclesCount' => $permanentAssignedVehiclesCount,
            'hasOutOfServiceSupport' => $hasOutOfServiceSupport,
            'outOfServiceVehiclesCount' => $outOfServiceVehiclesCount,
            'activeTripsCount' => $activeTripsCount,
            'trialDaysRemaining' => $trialDaysRemaining,
            'billingSummary' => $billingSummary,
            'vehicleReminders' => $vehicleReminders,
        ]);
    }
}
