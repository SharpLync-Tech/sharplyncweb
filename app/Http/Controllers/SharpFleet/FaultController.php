<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Mail\SharpFleet\FaultReported;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\FaultService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FaultController extends Controller
{
    protected FaultService $faultService;

    public function __construct(FaultService $faultService)
    {
        $this->faultService = $faultService;
    }

    public function storeFromTrip(Request $request): RedirectResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'trip_id' => ['required', 'integer'],
            'report_type' => ['required', 'string', 'in:issue,accident'],
            'severity' => ['required', 'string', 'in:minor,major,critical'],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $faultId = $this->faultService->createFaultFromTrip($user, $validated);
        $this->sendFaultNotification($user, $faultId, $validated);

        return back()->with('success', 'Vehicle issue/accident reported successfully.');
    }

    public function storeStandalone(Request $request): RedirectResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer'],
            'report_type' => ['required', 'string', 'in:issue,accident'],
            'severity' => ['required', 'string', 'in:minor,major,critical'],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $this->faultService->createFaultStandalone($user, $validated);

        return back()->with('success', 'Vehicle issue/accident reported successfully.');
    }

    private function sendFaultNotification(array $user, int $faultId, array $payload): void
    {
        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0 || $faultId <= 0) {
            return;
        }

        $settings = new CompanySettingsService($organisationId);
        if (!$settings->allowFaultsDuringTrip()) {
            return;
        }

        $recipients = $settings->faultDuringTripRecipients();
        if (empty($recipients)) {
            return;
        }

        $fault = DB::connection('sharpfleet')
            ->table('faults as f')
            ->leftJoin('vehicles as v', 'f.vehicle_id', '=', 'v.id')
            ->leftJoin('users as u', 'f.user_id', '=', 'u.id')
            ->select(
                'f.id',
                'f.trip_id',
                'f.severity',
                'f.title',
                'f.description',
                'f.occurred_at',
                'f.created_at',
                'v.name as vehicle_name',
                'v.registration_number as vehicle_registration_number',
                'u.first_name as user_first_name',
                'u.last_name as user_last_name',
                'u.email as user_email'
            )
            ->where('f.organisation_id', $organisationId)
            ->where('f.id', $faultId)
            ->first();

        if (!$fault) {
            return;
        }

        $organisationName = (string) (DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', $organisationId)
            ->value('name') ?? '');

        $tz = $settings->timezone();
        $dateFormat = $settings->dateFormat();
        $timeFormat = $settings->timeFormat();
        $formatDateTime = function (?string $value) use ($tz, $dateFormat, $timeFormat): ?string {
            if (!$value) {
                return null;
            }
            try {
                $format = trim($timeFormat) !== '' ? ($dateFormat . ' ' . $timeFormat) : $dateFormat;
                return Carbon::parse($value)->timezone($tz)->format($format);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $reporterName = trim((string) (($fault->user_first_name ?? '') . ' ' . ($fault->user_last_name ?? '')));
        if ($reporterName === '') {
            $reporterName = 'Driver';
        }

        $reportType = isset($payload['report_type']) ? (string) $payload['report_type'] : 'issue';
        $adminUrl = url('/app/sharpfleet/admin/faults');

        try {
            Mail::to($recipients)->send(new FaultReported(
                organisationName: $organisationName,
                reportType: $reportType,
                severity: (string) ($fault->severity ?? 'minor'),
                vehicleName: (string) ($fault->vehicle_name ?? 'Vehicle'),
                vehicleRegistration: (string) ($fault->vehicle_registration_number ?? ''),
                reporterName: $reporterName,
                reporterEmail: (string) ($fault->user_email ?? ''),
                occurredAt: $formatDateTime($fault->occurred_at ?? null),
                title: $fault->title !== null ? (string) $fault->title : null,
                description: (string) ($fault->description ?? ''),
                tripId: $fault->trip_id !== null ? (int) $fault->trip_id : null,
                reportedAt: (string) ($formatDateTime($fault->created_at ?? null) ?? ''),
                adminUrl: $adminUrl
            ));
        } catch (\Throwable $e) {
            Log::error('[SharpFleet Faults] Failed to send fault notification', [
                'organisation_id' => $organisationId,
                'fault_id' => $faultId,
                'recipients' => $recipients,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
