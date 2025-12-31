<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\VehicleReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || ($user['role'] ?? null) !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'Missing organisation');
        }

        $settingsService = new CompanySettingsService($organisationId);

        $timezone = $settingsService->timezone();
        $dateFormat = $settingsService->dateFormat();

        $regoEnabled = $settingsService->vehicleRegistrationTrackingEnabled();
        $serviceEnabled = $settingsService->vehicleServicingTrackingEnabled();

        $allSettings = $settingsService->all();
        $registrationDays = (int) ($allSettings['vehicles']['reminders']['registration_days'] ?? 30);
        $serviceDays = (int) ($allSettings['vehicles']['reminders']['service_days'] ?? 30);
        $serviceReadingThreshold = (int) ($allSettings['vehicles']['reminders']['service_reading_threshold'] ?? 500);

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
                timezone: $timezone
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
