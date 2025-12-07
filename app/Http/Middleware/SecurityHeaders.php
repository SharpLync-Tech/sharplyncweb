<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // --- Strict Transport Security ---
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        // --- Content Security Policy (CSP) ---
        $response->headers->set(
            'Content-Security-Policy',
                "default-src 'self' https://sharplync.com.au https://sharplink.com.au https://*.azurewebsites.net; " .

                // IMAGES (Google Maps + GA + local + inlined data)
                "img-src 'self' data: https: http: https://maps.gstatic.com https://maps.googleapis.com https://www.google-analytics.com; " .

                // STYLES (Google Fonts + inline needed for Blade/Tailwind + unpkg)
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com; " .

                // FONTS (Google Fonts + local)
                "font-src 'self' data: https://fonts.gstatic.com; " .

                // SCRIPTS (GA4 + GTM + Maps + unpkg + inline shadow DOM)
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' "
                    . "https://maps.googleapis.com "
                    . "https://maps.gstatic.com "
                    . "https://unpkg.com "
                    . "https://www.googletagmanager.com "
                    . "https://www.google-analytics.com "
                    . "https:; " .

                // OUTBOUND NETWORK CALLS (GA4 + GTM + Maps)
                "connect-src 'self' "
                    . "https://maps.googleapis.com "
                    . "https://maps.gstatic.com "
                    . "https://unpkg.com "
                    . "https://www.google-analytics.com "
                    . "https://www.googletagmanager.com; " .

                // Frames
                "frame-src 'self';"
        );

        // --- Frame and Embedding Control ---
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // --- MIME Sniffing Protection ---
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // --- Referrer Policy ---
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

        // --- XSS Protection (legacy but harmless) ---
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // --- Permissions Policy ---
        $response->headers->set(
            'Permissions-Policy',
            "geolocation=(), microphone=(), camera=(), payment=(), usb=()"
        );

        return $response;
    }
}
