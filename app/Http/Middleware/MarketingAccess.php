<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Marketing\MarketingUser;
use Symfony\Component\HttpFoundation\Response;

class MarketingAccess
{
    /**
     * Ensure the signed-in admin is allowed to access marketing tools.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Session::get('admin_user');

        if (!$admin) {
            return redirect('/admin/login');
        }

        $email = strtolower((string) ($admin['userPrincipalName'] ?? $admin['mail'] ?? ''));

        if ($email == '') {
            abort(403, 'Marketing access only.');
        }

        $marketingUser = MarketingUser::where('email', $email)
            ->where('is_active', 1)
            ->first();

        if (!$marketingUser) {
            abort(403, 'Marketing access only.');
        }

        Session::put('marketing_user', [
            'email' => $marketingUser->email,
            'role' => $marketingUser->role,
            'brand_scope' => $marketingUser->brand_scope,
        ]);

        return $next($request);
    }
}
