<?php
/**
 * SharpLync Microsoft 365 Login Controller
 * Version: 1.0
 */

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class MicrosoftController extends Controller
{
    public function redirectToMicrosoft()
    {
        $state = Str::random(64);
        Session::put('admin_oauth_state', $state);

        $params = [
            'client_id' => env('MICROSOFT_CLIENT_ID'),
            'response_type' => 'code',
            'redirect_uri' => env('MICROSOFT_REDIRECT_URI'),
            'response_mode' => 'query',
            'scope' => 'openid profile email User.Read',
            'state' => $state,
        ];

        $authorizeUrl = "https://login.microsoftonline.com/" .
            env('MICROSOFT_TENANT_ID') .
            "/oauth2/v2.0/authorize?" . http_build_query($params);

        return redirect($authorizeUrl);
    }

    public function handleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect('/')->with('error', $request->get('error_description'));
        }

        $expectedState = (string) Session::get('admin_oauth_state', '');
        $providedState = (string) $request->get('state', '');
        Session::forget('admin_oauth_state');

        if ($expectedState === '' || $providedState === '' || !hash_equals($expectedState, $providedState)) {
            return response('Authentication failed (invalid state).', 419);
        }

        $response = Http::asForm()->post(
            "https://login.microsoftonline.com/" . env('MICROSOFT_TENANT_ID') . "/oauth2/v2.0/token",
            [
                'grant_type' => 'authorization_code',
                'client_id' => env('MICROSOFT_CLIENT_ID'),
                'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
                'redirect_uri' => env('MICROSOFT_REDIRECT_URI'),
                'code' => $request->get('code'),
            ]
        );

        $tokens = $response->json();

        if (!isset($tokens['access_token'])) {
            return redirect('/')->with('error', 'Authentication failed.');
        }

        $userinfo = Http::withToken($tokens['access_token'])
            ->get('https://graph.microsoft.com/v1.0/me')
            ->json();

        $upn = strtolower((string)($userinfo['userPrincipalName'] ?? ''));

        // Only allow SharpLync users
        if ($upn === '' || !str_ends_with($upn, '@sharplync.com.au')) {
            return response('Unauthorized: not a SharpLync account', 403);
        }

        // Prevent session fixation
        $request->session()->regenerate();

        Session::put('admin_user', $userinfo);
        return redirect('/admin/dashboard');
    }

    public function logout()
    {
        Session::forget('admin_user');

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    }
}