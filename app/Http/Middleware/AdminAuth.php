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
        if (!isset($user['userPrincipalName']) ||
            !str_ends_with($user['userPrincipalName'], '@sharplync.com.au')) {
            Session::forget('admin_user');
            return response('Unauthorized – Invalid domain.', 403);
        }

        return $next($request);
    }
}