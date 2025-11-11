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

class MicrosoftController extends Controller
{
    public function redirectToMicrosoft()
    {
        $params = [
            'client_id' => env('MICROSOFT_CLIENT_ID'),
            'response_type' => 'code',
            'redirect_uri' => env('MICROSOFT_REDIRECT_URI'),
            'response_mode' => 'query',
            'scope' => 'openid profile email User.Read',
            'state' => csrf_token(),
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

        // Only allow SharpLync users
        if (!str_ends_with($userinfo['userPrincipalName'], '@sharplync.com.au')) {
            return response('Unauthorized: not a SharpLync account', 403);
        }

        Session::put('admin_user', $userinfo);
        return redirect('/admin/dashboard');
    }

    public function logout()
    {
        Session::forget('admin_user');
        return redirect('/');
    }
}