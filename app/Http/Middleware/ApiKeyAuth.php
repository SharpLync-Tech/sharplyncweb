<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\SharpFleet\User;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = $request->header('X-API-KEY');

        if (!$providedKey) {
            return response()->json(['error' => 'API key missing'], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Allow system / internal API key (Azure env)
        |--------------------------------------------------------------------------
        */
        $systemKey = env('API_SECRET_KEY');

        if ($systemKey && hash_equals($systemKey, $providedKey)) {
            Log::info('🔐 API Key Auth: system key accepted');
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Allow per-user API key (mobile)
        |--------------------------------------------------------------------------
        */
        $user = User::where('api_key', $providedKey)->first();

        if ($user) {
            // Attach user to request for downstream use
            $request->setUserResolver(fn () => $user);

            Log::info('🔐 API Key Auth: user key accepted', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Reject everything else
        |--------------------------------------------------------------------------
        */
        Log::warning('❌ API Key Auth failed', [
            'provided_key' => substr($providedKey, 0, 8) . '...',
        ]);

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
