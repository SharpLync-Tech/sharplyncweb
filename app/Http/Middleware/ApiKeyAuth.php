<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log; // 👈 Add this

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = env('API_KEY');
        $providedKey = $request->header('X-API-KEY');

        // 🪵 Add this log to debug values
        Log::info('🔐 API Key Auth Debug', [
            'env_api_key' => $apiKey,
            'provided_key' => $providedKey,
            'match' => $apiKey === $providedKey,
        ]);

        if (!$providedKey || $providedKey !== $apiKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
