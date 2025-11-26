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
        // Forces HTTPS connections for 1 year, including subdomains
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        // --- Content Security Policy (CSP) ---
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self' https://sharplync.com.au https://sharplink.com.au https://*.azurewebsites.net; " .

                // IMAGES (Google Maps + Your Storage)
                "img-src 'self' data: https: http: https://maps.gstatic.com https://maps.googleapis.com; " .

                // STYLES (Allow inline + Google Fonts + unpkg for component styles)
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com; " .

                // FONTS (Google fonts + local)
                "font-src 'self' data: https://fonts.gstatic.com; " .

                // SCRIPTS (critical: allow Google Maps + gmpx extended components + inline shadow DOM scripts)
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://maps.googleapis.com https://maps.gstatic.com https://unpkg.com https:; " .

                // NETWORK/API CALLS
                "connect-src 'self' https://maps.googleapis.com https://maps.gstatic.com https://unpkg.com; " .

                // FRAMES (safe)
                "frame-src 'self';"
            );


        // --- Frame and Embedding Control ---
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // --- MIME Sniffing Protection ---
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // --- Referrer Policy ---
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

        // --- XSS Protection (legacy, still useful for old browsers) ---
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // --- Permissions Policy ---
        // Controls browser features like camera, microphone, geolocation, etc.
        $response->headers->set(
            'Permissions-Policy',
            "geolocation=(), microphone=(), camera=(), payment=(), usb=()"
        );

        return $response;
    }
}
