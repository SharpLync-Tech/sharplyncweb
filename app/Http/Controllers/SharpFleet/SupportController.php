<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function show(Request $request)
    {
        $user = $this->requireUser($request);

        $organisationName = (string) (DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', (int) $user['organisation_id'])
            ->value('name') ?? '');

        $redirectUrl = Roles::isAdminPortal($user)
            ? '/app/sharpfleet/admin'
            : '/app/sharpfleet/driver';

        return view('sharpfleet.support', [
            'user' => $user,
            'organisationName' => $organisationName,
            'redirectUrl' => $redirectUrl,
        ]);
    }

    public function send(Request $request)
    {
        $user = $this->requireUser($request);

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
            'SharpFleet Support Request',
            '--------------------------',
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
                    ->subject('SharpFleet Support Request');

                if ($email !== '') {
                    $message->replyTo($email, $name !== '' ? $name : null);
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not send your support request. Please try again.');
        }

        return back()->with('success', 'Support request sent. We will get back to you shortly.');
    }

    private function requireUser(Request $request): array
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !is_array($user)) {
            abort(403);
        }

        $role = Roles::normalize((string) ($user['role'] ?? ''));
        if (!in_array($role, [
            Roles::COMPANY_ADMIN,
            Roles::BRANCH_ADMIN,
            Roles::BOOKING_ADMIN,
            Roles::DRIVER,
        ], true)) {
            abort(403);
        }

        return $user;
    }
}
