<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'app/sharpfleet/stripe/webhook',
    ];

    protected function shouldPassThrough($request): bool
    {
        if ($this->isSharpFleetMobileRequest($request) && $this->hasMobileTokenHeader($request)) {
            return true;
        }

        return parent::shouldPassThrough($request);
    }

    private function isSharpFleetMobileRequest(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        return str_starts_with($path, '/app/sharpfleet/mobile/')
            || $path === '/app/sharpfleet/mobile'
            || str_starts_with($path, '/app/sharpfleet/trips/')
            || str_starts_with($path, '/app/sharpfleet/faults/')
            || str_starts_with($path, '/app/sharpfleet/bookings/')
            || $path === '/app/sharpfleet/mobile/support'
            || $path === '/app/sharpfleet/mobile/fuel';
    }

    private function hasMobileTokenHeader(Request $request): bool
    {
        $auth = (string) $request->header('Authorization', '');
        if ($auth !== '' && str_starts_with(strtolower($auth), 'bearer ')) {
            return true;
        }

        return (string) $request->header('X-Device-Token', '') !== '';
    }
}
