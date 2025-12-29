<?php

namespace App\Http\Middleware;

use App\Services\SharpFleet\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SharpFleetAuditLog
{
    private AuditLogService $audit;

    public function __construct(AuditLogService $audit)
    {
        $this->audit = $audit;
    }

    /**
     * Log authenticated SharpFleet tenant actions (non-GET) with status + latency.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $method = strtoupper((string) $request->method());

        // Default: log mutating actions only (keeps noise/cost down).
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $started = microtime(true);

        $inputKeys = array_keys($request->except([
            'password',
            'password_confirmation',
            'token',
            'remember_token',
        ]));

        try {
            /** @var Response $response */
            $response = $next($request);
        } catch (\Throwable $e) {
            $durationMs = (microtime(true) - $started) * 1000;

            $this->audit->logSubscriberRequest(
                $request,
                $this->friendlyActionName($request),
                500,
                $durationMs,
                [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'input_keys' => $inputKeys,
                    ...$this->friendlyContext($request, $inputKeys),
                ]
            );

            throw $e;
        }

        $durationMs = (microtime(true) - $started) * 1000;

        $this->audit->logSubscriberRequest(
            $request,
            $this->friendlyActionName($request),
            $response->getStatusCode(),
            $durationMs,
            [
                'input_keys' => $inputKeys,
                ...$this->friendlyContext($request, $inputKeys),
            ]
        );

        return $response;
    }

    private function friendlyActionName(Request $request): string
    {
        $method = strtoupper((string) $request->method());
        $path = '/' . ltrim((string) $request->path(), '/');

        // Human-friendly labels for key tenant actions
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/settings') {
            return 'Updated Company Settings';
        }

        // Admin portal (tenant)
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/company/profile') {
            return 'Updated Company Profile';
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/safety-checks') {
            return 'Updated Safety Check Definition';
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/customers') {
            return 'Added Customer';
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/users/invite') {
            return 'Invited Driver';
        }

        // Vehicles (tenant admin)
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/vehicles') {
            return 'Created Vehicle';
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/vehicles/(\\d+)$#', $path, $m)) {
            return 'Updated Vehicle #' . $m[1];
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/vehicles/(\\d+)/archive$#', $path, $m)) {
            return 'Archived Vehicle #' . $m[1];
        }

        // Bookings (tenant admin)
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/bookings') {
            return 'Created Booking';
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/bookings/(\\d+)/change-vehicle$#', $path, $m)) {
            return 'Changed Booking Vehicle #' . $m[1];
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/bookings/(\\d+)/cancel$#', $path, $m)) {
            return 'Cancelled Booking #' . $m[1];
        }

        // Faults (tenant admin)
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/faults/(\\d+)/status$#', $path, $m)) {
            return 'Updated Fault Status #' . $m[1];
        }

        // Users (tenant admin)
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/users/(\\d+)/resend-invite$#', $path, $m)) {
            return 'Resent Driver Invite #' . $m[1];
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/users/(\\d+)$#', $path, $m)) {
            return 'Updated User #' . $m[1];
        }

        // Driver portal actions
        if ($method === 'POST' && $path === '/app/sharpfleet/trips/start') {
            return 'Started Trip';
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/trips/end') {
            return 'Ended Trip';
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/trips/offline-sync') {
            return 'Synced Offline Trips';
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/faults/from-trip') {
            return 'Reported Fault (From Trip)';
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/faults/standalone') {
            return 'Reported Fault (Standalone)';
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/bookings') {
            return 'Created Booking';
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/bookings/(\\d+)/cancel$#', $path, $m)) {
            return 'Cancelled Booking #' . $m[1];
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/bookings/start-trip') {
            return 'Started Trip From Booking';
        }

        // Fallbacks
        $route = $request->route();
        $name = $route ? $route->getName() : null;
        if (is_string($name) && $name !== '') {
            return 'sharpfleet.route.' . $name;
        }

        return 'sharpfleet.request';
    }

    private function friendlyContext(Request $request, array $inputKeys): array
    {
        $method = strtoupper((string) $request->method());
        $path = '/' . ltrim((string) $request->path(), '/');

        if ($method === 'POST' && $path === '/app/sharpfleet/admin/settings') {
            $sections = [];

            $hasAny = fn (array $keys) => count(array_intersect($keys, $inputKeys)) > 0;

            if ($hasAny(['enable_client_presence', 'require_client_presence', 'client_label', 'enable_client_addresses'])) {
                $sections[] = 'Passenger / Client Presence';
            }
            if ($hasAny(['enable_customer_capture', 'allow_customer_select', 'allow_customer_manual', 'require_customer_capture'])) {
                $sections[] = 'Customer Capture';
            }
            if ($hasAny(['require_odometer_start', 'allow_odometer_override'])) {
                $sections[] = 'Odometer Rules';
            }
            if ($hasAny(['allow_private_trips'])) {
                $sections[] = 'Private Trips';
            }
            if ($hasAny(['enable_vehicle_registration_tracking'])) {
                $sections[] = 'Vehicle Registration Tracking';
            }
            if ($hasAny(['enable_vehicle_servicing_tracking'])) {
                $sections[] = 'Vehicle Servicing Tracking';
            }
            if ($hasAny(['enable_safety_check', 'require_safety_check'])) {
                $sections[] = 'Safety Check';
            }

            $summary = 'Company settings updated';
            if (!empty($sections)) {
                $summary .= ': ' . implode(', ', $sections);
            }

            return [
                'summary' => $summary,
                'changed_sections' => $sections,
                'changes' => [
                    // Safe, support-friendly values (no passwords/tokens)
                    'enable_client_presence' => $request->boolean('enable_client_presence'),
                    'require_client_presence' => $request->boolean('require_client_presence'),
                    'client_label' => (string) $request->input('client_label', ''),
                ],
            ];
        }

        // Generic summaries for common actions, without logging sensitive values
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/vehicles/(\\d+)/archive$#', $path, $m)) {
            return ['summary' => 'Vehicle archived (ID ' . $m[1] . ')'];
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/vehicles/(\\d+)$#', $path, $m)) {
            return ['summary' => 'Vehicle updated (ID ' . $m[1] . ')'];
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/vehicles') {
            return ['summary' => 'Vehicle created'];
        }

        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/bookings/(\\d+)/change-vehicle$#', $path, $m)) {
            return ['summary' => 'Booking vehicle changed (Booking ID ' . $m[1] . ')'];
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/bookings/(\\d+)/cancel$#', $path, $m)) {
            return ['summary' => 'Booking cancelled (Booking ID ' . $m[1] . ')'];
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/admin/bookings') {
            return ['summary' => 'Booking created'];
        }

        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/faults/(\\d+)/status$#', $path, $m)) {
            return ['summary' => 'Fault status updated (Fault ID ' . $m[1] . ')'];
        }

        if ($method === 'POST' && $path === '/app/sharpfleet/admin/users/invite') {
            return ['summary' => 'Driver invited'];
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/users/(\\d+)/resend-invite$#', $path, $m)) {
            return ['summary' => 'Driver invite resent (User ID ' . $m[1] . ')'];
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/admin/users/(\\d+)$#', $path, $m)) {
            return ['summary' => 'User updated (User ID ' . $m[1] . ')'];
        }

        if ($method === 'POST' && $path === '/app/sharpfleet/trips/start') {
            return ['summary' => 'Trip started'];
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/trips/end') {
            return ['summary' => 'Trip ended'];
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/trips/offline-sync') {
            return ['summary' => 'Offline trips synced'];
        }

        if ($method === 'POST' && $path === '/app/sharpfleet/faults/from-trip') {
            return ['summary' => 'Fault reported from trip'];
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/faults/standalone') {
            return ['summary' => 'Standalone fault reported'];
        }

        if ($method === 'POST' && $path === '/app/sharpfleet/bookings') {
            return ['summary' => 'Booking created'];
        }
        if ($method === 'POST' && preg_match('#^/app/sharpfleet/bookings/(\\d+)/cancel$#', $path, $m)) {
            return ['summary' => 'Booking cancelled (Booking ID ' . $m[1] . ')'];
        }
        if ($method === 'POST' && $path === '/app/sharpfleet/bookings/start-trip') {
            return ['summary' => 'Trip started from booking'];
        }

        return [];
    }
}
