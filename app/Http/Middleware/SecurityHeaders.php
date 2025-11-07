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
        // Allows assets from your own domains + Azure + HTTPS sources
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self' https://sharplync.com.au https://sharplink.com.au https://*.azurewebsites.net; ".
            "img-src 'self' data: https: http:; ".
            "style-src 'self' 'unsafe-inline'; ".
            "font-src 'self' data:; ".
            "script-src 'self' 'unsafe-inline' https:;"
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
