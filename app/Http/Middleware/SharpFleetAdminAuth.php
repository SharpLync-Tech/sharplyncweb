<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SharpFleetAdminAuth
{
    private const ARCHIVED_LOGIN_MESSAGE = 'This account has been archived. Please contact your administrator.';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        // Not logged into SharpFleet at all
        if (!$fleetUser || empty($fleetUser['logged_in'])) {
            return redirect('/app/sharpfleet/login');
        }

        if ($this->isArchivedUserId((int) ($fleetUser['id'] ?? 0))) {
            $request->session()->forget('sharpfleet.user');
            Cookie::queue(Cookie::forget('sharpfleet_remember'));
            return redirect('/app/sharpfleet/login')->withErrors([
                'email' => self::ARCHIVED_LOGIN_MESSAGE,
            ]);
        }

        // Logged in, but not an admin
        if (($fleetUser['role'] ?? null) !== 'admin') {
            abort(403, 'SharpFleet admin access only.');
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
