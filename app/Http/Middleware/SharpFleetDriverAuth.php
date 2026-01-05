<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SharpFleetDriverAuth
{
    private const ARCHIVED_LOGIN_MESSAGE = 'This account has been archived. Please contact your administrator.';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('sharpfleet.user');

        // Not logged into SharpFleet
        if (!$user) {
            return redirect('/app/sharpfleet/login');
        }

        if ($this->isArchivedUserId((int) ($user['id'] ?? 0))) {
            $request->session()->forget('sharpfleet.user');
            Cookie::queue(Cookie::forget('sharpfleet_remember'));
            return redirect('/app/sharpfleet/login')->withErrors([
                'email' => self::ARCHIVED_LOGIN_MESSAGE,
            ]);
        }

        // Logged in but not a driver
        // Allow driver or admin (for sole traders/admins who drive)
        if (!in_array($user['role'] ?? null, ['driver', 'admin'])) {
            return response()->view('sharpfleet.errors.driver-denied', [], 403);
        }

        // Check is_driver flag for granular control
        if (empty($user['is_driver'])) {
            return response()->view('sharpfleet.errors.driver-denied', [], 403);
        }

        return $next($request);
    }

    private function isArchivedUserId(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        if (!Schema::connection('sharpfleet')->hasColumn('users', 'archived_at')) {
            return false;
        }

        $archivedAt = DB::connection('sharpfleet')
            ->table('users')
            ->where('id', $userId)
            ->value('archived_at');

        return !empty($archivedAt);
    }
}
