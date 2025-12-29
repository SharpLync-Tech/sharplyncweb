<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SharpFleetNoStore
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Ensure authenticated HTML pages behave like a normal browser session in the PWA:
        // always revalidate when online (service worker already falls back to cache when offline).
        if ($request->is('app/sharpfleet*') && $request->isMethod('GET')) {
            $contentType = (string) $response->headers->get('Content-Type', '');
            $isHtml = str_contains($contentType, 'text/html');

            if ($isHtml) {
                $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
        }

        return $response;
    }
}
