<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && strtolower($request->getHost()) === 'www.sharplync.com.au') {
            return redirect()->away(
                rtrim((string) config('seo.site_url'), '/') . $request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }
}
