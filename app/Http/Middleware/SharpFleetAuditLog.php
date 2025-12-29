<?php

namespace App\Http\Middleware;

use App\Services\SharpFleet\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SharpFleetAuditLog
{
    private AuditLogService $audit;

    public function __construct(AuditLogService $audit)
    {
        $this->audit = $audit;
    }

    /**
     * Log authenticated SharpFleet tenant actions (non-GET) with status + latency.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $method = strtoupper((string) $request->method());

        // Default: log mutating actions only (keeps noise/cost down).
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $started = microtime(true);

        try {
            /** @var Response $response */
            $response = $next($request);
        } catch (\Throwable $e) {
            $durationMs = (microtime(true) - $started) * 1000;

            $this->audit->logSubscriberRequest(
                $request,
                $this->actionName($request),
                500,
                $durationMs,
                [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        $durationMs = (microtime(true) - $started) * 1000;

        $this->audit->logSubscriberRequest(
            $request,
            $this->actionName($request),
            $response->getStatusCode(),
            $durationMs,
            [
                'input_keys' => array_keys($request->except([
                    'password',
                    'password_confirmation',
                    'token',
                    'remember_token',
                ])),
            ]
        );

        return $response;
    }

    private function actionName(Request $request): string
    {
        $route = $request->route();
        $name = $route ? $route->getName() : null;

        if (is_string($name) && $name !== '') {
            return 'sharpfleet.route.' . $name;
        }

        return 'sharpfleet.request';
    }
}
