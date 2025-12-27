<?php
/**
 * SharpLync Admin Authentication Middleware
 * Version: 1.0
 * Description:
 *   - Ensures only authenticated SharpLync M365 users can access admin routes
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Session::get('admin_user');

        // If no session, redirect to Microsoft login
        if (!$user) {
            return redirect('/admin/login');
        }

        // Ensure email domain restriction
        $upn = strtolower((string)($user['userPrincipalName'] ?? ''));

        if ($upn === '' || !str_ends_with($upn, '@sharplync.com.au')) {
            Session::forget('admin_user');
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return response('Unauthorized – Invalid domain.', 403);
        }

        return $next($request);
    }
}