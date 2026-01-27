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

                // IMAGES (Google Maps + GA + Google Ads + local + inline)
                "img-src 'self' data: https: http: "
                    . "https://maps.gstatic.com "
                    . "https://maps.googleapis.com "
                    . "https://www.google-analytics.com "
                    . "https://googleads.g.doubleclick.net "
                    . "https://www.googleadservices.com "
                    . "https://www.googletagmanager.com; " .

                // STYLES
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com; " .

                // FONTS
                "font-src 'self' data: https://fonts.gstatic.com; " .

                // SCRIPTS (GA4 + GTM + Google Ads + Maps + inline)
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' "
                    . "https://maps.googleapis.com "
                    . "https://maps.gstatic.com "
                    . "https://unpkg.com "
                    . "https://www.googletagmanager.com "
                    . "https://www.google-analytics.com "
                    . "https://googleads.g.doubleclick.net "
                    . "https://www.googleadservices.com "
                    . "https:; " .

                // CONNECT (GA4 + GTM + Google Ads + Maps)
                "connect-src 'self' "
                    . "https://maps.googleapis.com "
                    . "https://maps.gstatic.com "
                    . "https://unpkg.com "
                    . "https://www.google-analytics.com "
                    . "https://www.googletagmanager.com "
                    . "https://googleads.g.doubleclick.net "
                    . "https://www.googleadservices.com; " .

                // Frames
                "frame-src 'self' https://www.googletagmanager.com https://www.youtube.com https://www.youtube-nocookie.com;"

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
