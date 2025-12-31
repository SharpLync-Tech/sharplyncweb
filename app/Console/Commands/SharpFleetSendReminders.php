<?php

namespace App\Console\Commands;

use App\Mail\SharpFleet\RegoReminderDigest;
use App\Mail\SharpFleet\ServiceReminderDigest;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\VehicleReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SharpFleetSendReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sharpfleet:send-reminders
        {--org= : Only process a single organisation id}
        {--dry-run=1 : Log only; do not send emails}
        {--registration-days= : Override rego reminder window (days)}
        {--service-days= : Override service-by-date reminder window (days)}
        {--service-reading-threshold= : Override service-by-reading threshold (km/hours)}';

    /**
     * The console command description.
     */
    protected $description = 'Build SharpFleet rego + servicing reminder digest (dry-run logging only)';

    public function handle(VehicleReminderService $reminderService): int
    {
        $onlyOrg = $this->option('org');
        $dryRun = (bool) filter_var((string) $this->option('dry-run'), FILTER_VALIDATE_BOOLEAN);

        $hasReminderLog = Schema::connection('sharpfleet')->hasTable('vehicle_reminder_log');

        $orgQuery = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name');

        if (!empty($onlyOrg)) {
            $orgQuery->where('id', (int) $onlyOrg);
        }

        $organisations = $orgQuery->orderBy('id')->get();

        if ($organisations->isEmpty()) {
            $this->info('No organisations found to process.');
            return self::SUCCESS;
        }

        foreach ($organisations as $org) {
            $organisationId = (int) $org->id;

            $settings = new CompanySettingsService($organisationId);
            $tz = $settings->timezone();

            $registrationTrackingEnabled = $settings->vehicleRegistrationTrackingEnabled();
            $servicingTrackingEnabled = $settings->vehicleServicingTrackingEnabled();

            if (!$registrationTrackingEnabled && !$servicingTrackingEnabled) {
                Log::info('[SharpFleet Reminders] Skipped org (tracking disabled)', [
                    'organisation_id' => $organisationId,
                    'organisation_name' => $org->name ?? null,
                ]);
                continue;
            }

            $registrationDays = $settings->reminderRegistrationDays();
            $serviceDays = $settings->reminderServiceDays();
            $serviceReadingThreshold = $settings->reminderServiceReadingThreshold();

            if ($this->option('registration-days') !== null) {
                $registrationDays = (int) $this->option('registration-days');
            }

            if ($this->option('service-days') !== null) {
                $serviceDays = (int) $this->option('service-days');
            }

            if ($this->option('service-reading-threshold') !== null) {
                $serviceReadingThreshold = (int) $this->option('service-reading-threshold');
            }

            $digest = $reminderService->buildDigest(
                organisationId: $organisationId,
                registrationDays: $registrationDays,
                serviceDays: $serviceDays,
                serviceReadingThreshold: $serviceReadingThreshold,
                timezone: $tz
            );

            if (!$registrationTrackingEnabled) {
                $digest['registration'] = ['overdue' => [], 'due_soon' => []];
            }

            if (!$servicingTrackingEnabled) {
                $digest['serviceDate'] = ['overdue' => [], 'due_soon' => []];
                $digest['serviceReading'] = ['overdue' => [], 'due_soon' => []];
            }

            $todayOrg = Carbon::now($tz)->toDateString();

            $recipient = $this->resolveSubscriberAdminEmail($organisationId);

            $counts = [
                'rego_overdue' => count($digest['registration']['overdue'] ?? []),
                'rego_due_soon' => count($digest['registration']['due_soon'] ?? []),
                'service_date_overdue' => count($digest['serviceDate']['overdue'] ?? []),
                'service_date_due_soon' => count($digest['serviceDate']['due_soon'] ?? []),
                'service_reading_overdue' => count($digest['serviceReading']['overdue'] ?? []),
                'service_reading_due_soon' => count($digest['serviceReading']['due_soon'] ?? []),
            ];

            Log::info('[SharpFleet Reminders] Digest built', [
                'organisation_id' => $organisationId,
                'organisation_name' => $org->name ?? null,
                'timezone' => $tz,
                'today' => $todayOrg,
                'dry_run' => $dryRun,
                'recipient' => $recipient,
                'counts' => $counts,
                'settings' => $digest['settings'] ?? [],
                'dedupe_table_present' => $hasReminderLog,
            ]);

            if (!$recipient) {
                Log::warning('[SharpFleet Reminders] No subscriber admin email found; skipping email send', [
                    'organisation_id' => $organisationId,
                    'organisation_name' => $org->name ?? null,
                ]);
                continue;
            }

            // Per-day dedupe if table exists
            $regoOverdue = $this->filterItemsToSend($organisationId, $tz, $digest['registration']['overdue'] ?? [], 'rego_overdue', $hasReminderLog);
            $regoDueSoon = $this->filterItemsToSend($organisationId, $tz, $digest['registration']['due_soon'] ?? [], 'rego_due_soon', $hasReminderLog);
            $serviceDateOverdue = $this->filterItemsToSend($organisationId, $tz, $digest['serviceDate']['overdue'] ?? [], 'service_date_overdue', $hasReminderLog);
            $serviceDateDueSoon = $this->filterItemsToSend($organisationId, $tz, $digest['serviceDate']['due_soon'] ?? [], 'service_date_due_soon', $hasReminderLog);
            $serviceReadingOverdue = $this->filterItemsToSend($organisationId, $tz, $digest['serviceReading']['overdue'] ?? [], 'service_reading_overdue', $hasReminderLog);
            $serviceReadingDueSoon = $this->filterItemsToSend($organisationId, $tz, $digest['serviceReading']['due_soon'] ?? [], 'service_reading_due_soon', $hasReminderLog);

            // Email: registration
            if (!empty($regoOverdue) || !empty($regoDueSoon)) {
                if ($dryRun) {
                    Log::info('[SharpFleet Reminders] Would email rego digest', [
                        'organisation_id' => $organisationId,
                        'recipient' => $recipient,
                        'counts' => [
                            'overdue' => count($regoOverdue),
                            'due_soon' => count($regoDueSoon),
                        ],
                    ]);
                } else {
                    try {
                        Mail::to($recipient)->send(new RegoReminderDigest(
                            organisationName: (string) ($org->name ?? ''),
                            overdue: $regoOverdue,
                            dueSoon: $regoDueSoon
                        ));

                        Log::info('[SharpFleet Reminders] Rego digest emailed', [
                            'organisation_id' => $organisationId,
                            'recipient' => $recipient,
                        ]);

                        if ($hasReminderLog) {
                            $this->persistReminderItems($organisationId, $tz, $regoOverdue, 'rego_overdue');
                            $this->persistReminderItems($organisationId, $tz, $regoDueSoon, 'rego_due_soon');
                        }
                    } catch (\Throwable $e) {
                        Log::error('[SharpFleet Reminders] Failed to email rego digest', [
                            'organisation_id' => $organisationId,
                            'recipient' => $recipient,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Email: servicing
            if (!empty($serviceDateOverdue) || !empty($serviceDateDueSoon) || !empty($serviceReadingOverdue) || !empty($serviceReadingDueSoon)) {
                if ($dryRun) {
                    Log::info('[SharpFleet Reminders] Would email service digest', [
                        'organisation_id' => $organisationId,
                        'recipient' => $recipient,
                        'counts' => [
                            'date_overdue' => count($serviceDateOverdue),
                            'date_due_soon' => count($serviceDateDueSoon),
                            'reading_overdue' => count($serviceReadingOverdue),
                            'reading_due_soon' => count($serviceReadingDueSoon),
                        ],
                    ]);
                } else {
                    try {
                        Mail::to($recipient)->send(new ServiceReminderDigest(
                            organisationName: (string) ($org->name ?? ''),
                            serviceDateOverdue: $serviceDateOverdue,
                            serviceDateDueSoon: $serviceDateDueSoon,
                            serviceReadingOverdue: $serviceReadingOverdue,
                            serviceReadingDueSoon: $serviceReadingDueSoon
                        ));

                        Log::info('[SharpFleet Reminders] Service digest emailed', [
                            'organisation_id' => $organisationId,
                            'recipient' => $recipient,
                        ]);

                        if ($hasReminderLog) {
                            $this->persistReminderItems($organisationId, $tz, $serviceDateOverdue, 'service_date_overdue');
                            $this->persistReminderItems($organisationId, $tz, $serviceDateDueSoon, 'service_date_due_soon');
                            $this->persistReminderItems($organisationId, $tz, $serviceReadingOverdue, 'service_reading_overdue');
                            $this->persistReminderItems($organisationId, $tz, $serviceReadingDueSoon, 'service_reading_due_soon');
                        }
                    } catch (\Throwable $e) {
                        Log::error('[SharpFleet Reminders] Failed to email service digest', [
                            'organisation_id' => $organisationId,
                            'recipient' => $recipient,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Emit per-vehicle “would send” lines (dry-run only; deduped if vehicle_reminder_log exists)
            if ($dryRun) {
                $this->logReminderItems($organisationId, $tz, $regoOverdue, 'rego_overdue');
                $this->logReminderItems($organisationId, $tz, $regoDueSoon, 'rego_due_soon');
                $this->logReminderItems($organisationId, $tz, $serviceDateOverdue, 'service_date_overdue');
                $this->logReminderItems($organisationId, $tz, $serviceDateDueSoon, 'service_date_due_soon');
                $this->logReminderItems($organisationId, $tz, $serviceReadingOverdue, 'service_reading_overdue');
                $this->logReminderItems($organisationId, $tz, $serviceReadingDueSoon, 'service_reading_due_soon');
            }
        }

        $this->info('SharpFleet reminders run completed (dry-run logging).');

        return self::SUCCESS;
    }

    private function logReminderItems(int $organisationId, string $timezone, array $items, string $type): void
    {
        foreach ($items as $item) {
            $vehicleId = (int) ($item['vehicle_id'] ?? 0);
            if ($vehicleId <= 0) {
                continue;
            }

            $dueDate = null;
            if (!empty($item['date'])) {
                try {
                    $dueDate = Carbon::instance($item['date'])->toDateString();
                } catch (\Exception $e) {
                    $dueDate = null;
                }
            }

            $dueReading = null;
            if (array_key_exists('due_reading', $item)) {
                $dueReading = $item['due_reading'] !== null ? (int) $item['due_reading'] : null;
            }

            Log::info('[SharpFleet Reminders] Would send reminder', [
                'organisation_id' => $organisationId,
                'vehicle_id' => $vehicleId,
                'type' => $type,
                'due_date' => $dueDate,
                'due_km' => $dueReading,
                'payload' => $item,
            ]);
        }
    }

    private function resolveSubscriberAdminEmail(int $organisationId): ?string
    {
        try {
            $orgColumns = Schema::connection('sharpfleet')->getColumnListing('organisations');
        } catch (\Throwable $e) {
            $orgColumns = [];
        }

        // Preferred: organisations.billing_email
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

        // Fallback: first admin user email for the organisation
        $hasUsersTable = Schema::connection('sharpfleet')->hasTable('users');
        if (!$hasUsersTable) {
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

    private function filterItemsToSend(int $organisationId, string $timezone, array $items, string $type, bool $hasReminderLog): array
    {
        if (!$hasReminderLog || empty($items)) {
            return $items;
        }

        $filtered = [];
        $loggedOn = Carbon::now($timezone)->toDateString();

        foreach ($items as $item) {
            $vehicleId = (int) ($item['vehicle_id'] ?? 0);
            if ($vehicleId <= 0) {
                continue;
            }

            [$dueDate, $dueReading] = $this->extractDueFields($item);

            $alreadyLogged = DB::connection('sharpfleet')
                ->table('vehicle_reminder_log')
                ->where('organisation_id', $organisationId)
                ->where('vehicle_id', $vehicleId)
                ->where('reminder_type', $type)
                ->where('logged_on', $loggedOn)
                ->when($dueDate !== null, fn ($q) => $q->where('due_date', $dueDate), fn ($q) => $q->whereNull('due_date'))
                ->when($dueReading !== null, fn ($q) => $q->where('due_km', $dueReading), fn ($q) => $q->whereNull('due_km'))
                ->exists();

            if ($alreadyLogged) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    private function persistReminderItems(int $organisationId, string $timezone, array $items, string $type): void
    {
        if (empty($items)) {
            return;
        }

        $loggedOn = Carbon::now($timezone)->toDateString();
        $nowUtc = Carbon::now('UTC')->toDateTimeString();

        foreach ($items as $item) {
            $vehicleId = (int) ($item['vehicle_id'] ?? 0);
            if ($vehicleId <= 0) {
                continue;
            }

            [$dueDate, $dueReading] = $this->extractDueFields($item);

            try {
                DB::connection('sharpfleet')
                    ->table('vehicle_reminder_log')
                    ->insert([
                        'organisation_id' => $organisationId,
                        'vehicle_id' => $vehicleId,
                        'reminder_type' => $type,
                        'due_date' => $dueDate,
                        'due_km' => $dueReading,
                        'logged_on' => $loggedOn,
                        'created_at' => $nowUtc,
                    ]);
            } catch (\Throwable $e) {
                // Ignore duplicates / insert errors to keep the job robust.
                continue;
            }
        }
    }

    private function extractDueFields(array $item): array
    {
        $dueDate = null;
        if (!empty($item['date'])) {
            try {
                $dueDate = Carbon::instance($item['date'])->toDateString();
            } catch (\Throwable $e) {
                $dueDate = null;
            }
        }

        $dueReading = null;
        if (array_key_exists('due_reading', $item)) {
            $dueReading = $item['due_reading'] !== null ? (int) $item['due_reading'] : null;
        }

        return [$dueDate, $dueReading];
    }
}
