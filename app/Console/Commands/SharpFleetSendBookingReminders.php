<?php

namespace App\Console\Commands;

use App\Mail\SharpFleet\BookingReminder;
use App\Services\SharpFleet\CompanySettingsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SharpFleetSendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sharpfleet:send-booking-reminders
        {--org= : Only process a single organisation id}
        {--dry-run=1 : Log only; do not send emails}
        {--window-minutes=10 : Minutes wide window around (now + 60 minutes)}';

    /**
     * The console command description.
     */
    protected $description = 'Send SharpFleet booking reminder emails (1 hour before start) for bookings opted-in via remind_me';

    public function handle(): int
    {
        $onlyOrg = $this->option('org');
        $dryRun = (bool) filter_var((string) $this->option('dry-run'), FILTER_VALIDATE_BOOLEAN);
        $windowMinutes = (int) ($this->option('window-minutes') ?? 10);
        if ($windowMinutes <= 0) {
            $windowMinutes = 10;
        }

        $this->info('[SharpFleet Booking Reminders] Starting run');
        $this->info('[SharpFleet Booking Reminders] Options: org=' . ($onlyOrg !== null ? (string) $onlyOrg : '') . ', dry_run=' . ($dryRun ? '1' : '0') . ', window_minutes=' . $windowMinutes);

        if (!Schema::connection('sharpfleet')->hasTable('organisations')) {
            $this->info('No organisations table found to process.');
            return self::SUCCESS;
        }

        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            $this->info('No bookings table found to process.');
            return self::SUCCESS;
        }

        if (!Schema::connection('sharpfleet')->hasColumn('bookings', 'remind_me')) {
            $this->info('Bookings.remind_me column not found; booking reminders are disabled.');
            return self::SUCCESS;
        }

        $hasBookingTimezone = Schema::connection('sharpfleet')->hasColumn('bookings', 'timezone');
        $hasCustomerId = Schema::connection('sharpfleet')->hasColumn('bookings', 'customer_id');
        $hasCustomerName = Schema::connection('sharpfleet')->hasColumn('bookings', 'customer_name');
        $hasReminderSentAt = Schema::connection('sharpfleet')->hasColumn('bookings', 'reminder_sent_at');

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

        $nowUtc = Carbon::now('UTC');
        $targetUtc = $nowUtc->copy()->addMinutes(60);
        $halfWindow = (int) floor($windowMinutes / 2);
        $windowStartUtc = $targetUtc->copy()->subMinutes($halfWindow);
        $windowEndUtc = $targetUtc->copy()->addMinutes($windowMinutes - $halfWindow);

        $this->info('[SharpFleet Booking Reminders] now_utc=' . $nowUtc->toDateTimeString());
        $this->info('[SharpFleet Booking Reminders] window_start_utc=' . $windowStartUtc->toDateTimeString() . ', window_end_utc=' . $windowEndUtc->toDateTimeString());

        foreach ($organisations as $org) {
            $organisationId = (int) ($org->id ?? 0);
            if ($organisationId <= 0) {
                continue;
            }

            $settings = new CompanySettingsService($organisationId);
            $companyTz = $settings->timezone();

            $query = DB::connection('sharpfleet')
                ->table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.id')
                ->leftJoin('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
                ->where('bookings.organisation_id', $organisationId)
                ->where('bookings.status', 'planned')
                ->where('bookings.remind_me', 1)
                ->whereBetween('bookings.planned_start', [
                    $windowStartUtc->toDateTimeString(),
                    $windowEndUtc->toDateTimeString(),
                ])
                ->where('bookings.planned_end', '>=', $nowUtc->toDateTimeString());

            if ($hasReminderSentAt) {
                $query->whereNull('bookings.reminder_sent_at');
            }

            $bookings = $query->select(
                'bookings.id as booking_id',
                'bookings.planned_start',
                'bookings.planned_end',
                'bookings.notes',
                $hasBookingTimezone ? 'bookings.timezone' : DB::raw("'' as timezone"),
                $hasCustomerId ? 'bookings.customer_id' : DB::raw('NULL as customer_id'),
                $hasCustomerName ? 'bookings.customer_name' : DB::raw("'' as customer_name"),
                'users.id as user_id',
                'users.email as user_email',
                'users.first_name as user_first_name',
                'users.last_name as user_last_name',
                'vehicles.name as vehicle_name',
                'vehicles.registration_number as vehicle_reg'
            )->orderBy('bookings.planned_start')->get();

            $this->info('[SharpFleet Booking Reminders] Organisation ' . $organisationId . ' bookings_to_process=' . $bookings->count());

            Log::info('[SharpFleet Booking Reminders] Scan', [
                'organisation_id' => $organisationId,
                'organisation_name' => $org->name ?? null,
                'dry_run' => $dryRun,
                'now_utc' => $nowUtc->toDateTimeString(),
                'window_start_utc' => $windowStartUtc->toDateTimeString(),
                'window_end_utc' => $windowEndUtc->toDateTimeString(),
                'booking_count' => $bookings->count(),
                'dedupe_column_present' => $hasReminderSentAt,
            ]);

            foreach ($bookings as $b) {
                $bookingId = (int) ($b->booking_id ?? 0);
                $email = is_string($b->user_email ?? null) ? trim((string) $b->user_email) : '';
                if ($bookingId <= 0 || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                // Dedupe: if we don't have a DB column, use cache as a lightweight guard.
                if (!$hasReminderSentAt) {
                    $key = 'sharpfleet:booking-reminder-sent:' . $organisationId . ':' . $bookingId;
                    $added = Cache::add($key, 1, now()->addHours(3));
                    if (!$added) {
                        continue;
                    }
                }

                $tz = $companyTz;
                if ($hasBookingTimezone) {
                    $bookingTz = is_string($b->timezone ?? null) ? trim((string) $b->timezone) : '';
                    if ($bookingTz !== '') {
                        $tz = $bookingTz;
                    }
                }

                $startLocal = Carbon::parse((string) $b->planned_start)->utc()->timezone($tz);
                $endLocal = Carbon::parse((string) $b->planned_end)->utc()->timezone($tz);

                $driverName = trim((string) (($b->user_first_name ?? '') . ' ' . ($b->user_last_name ?? '')));
                if ($driverName === '') {
                    $driverName = $email;
                }

                $vehicleName = is_string($b->vehicle_name ?? null) ? (string) $b->vehicle_name : '';
                $vehicleReg = is_string($b->vehicle_reg ?? null) ? (string) $b->vehicle_reg : '';

                $customerName = '';
                if ($hasCustomerName) {
                    $customerName = is_string($b->customer_name ?? null) ? trim((string) $b->customer_name) : '';
                }

                $notes = is_string($b->notes ?? null) ? trim((string) $b->notes) : '';

                if ($dryRun) {
                    Log::info('[SharpFleet Booking Reminders] Would send booking reminder', [
                        'organisation_id' => $organisationId,
                        'booking_id' => $bookingId,
                        'recipient' => $email,
                        'timezone' => $tz,
                        'start_local' => $startLocal->toDateTimeString(),
                        'vehicle' => $vehicleName,
                    ]);
                    $this->info('[SharpFleet Booking Reminders] DRY RUN would send booking_id=' . $bookingId . ' to ' . $email . ' start_local=' . $startLocal->toDateTimeString());
                    continue;
                }

                try {
                    // Force Mailgun for reminders to avoid silent no-op when the default mailer is set to "log".
                    Mail::mailer('mailgun')->to($email)->send(new BookingReminder(
                        driverName: $driverName,
                        timezone: $tz,
                        start: $startLocal,
                        end: $endLocal,
                        vehicleName: $vehicleName,
                        vehicleReg: $vehicleReg,
                        customerName: $customerName,
                        notes: $notes
                    ));

                    Log::info('[SharpFleet Booking Reminders] Booking reminder emailed', [
                        'organisation_id' => $organisationId,
                        'booking_id' => $bookingId,
                        'recipient' => $email,
                    ]);

                    $this->info('[SharpFleet Booking Reminders] Sent booking_id=' . $bookingId . ' to ' . $email);

                    if ($hasReminderSentAt) {
                        DB::connection('sharpfleet')
                            ->table('bookings')
                            ->where('organisation_id', $organisationId)
                            ->where('id', $bookingId)
                            ->update([
                                'reminder_sent_at' => $nowUtc->toDateTimeString(),
                                'updated_at' => $nowUtc->toDateTimeString(),
                            ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('[SharpFleet Booking Reminders] Failed sending booking reminder', [
                        'organisation_id' => $organisationId,
                        'booking_id' => $bookingId,
                        'recipient' => $email,
                        'error' => $e->getMessage(),
                    ]);

                    $this->error('[SharpFleet Booking Reminders] Failed booking_id=' . $bookingId . ' to ' . $email . ' error=' . $e->getMessage());
                }
            }
        }

        $this->info('[SharpFleet Booking Reminders] Run completed.');

        return self::SUCCESS;
    }
}
