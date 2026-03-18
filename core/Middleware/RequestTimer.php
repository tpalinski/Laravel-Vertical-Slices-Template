<?php

declare(strict_types=1);

namespace Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestTimer {
    public function handle(Request $request, Closure $next): Response {
        $startTime = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $endTime = microtime(true);
        $durationMs = round(($endTime - $startTime) * 1000, 2);

        Log::info('Request completed', [
            'route' => $request->path(),
            'duration_ms' => $durationMs,
            'method' => $request->method(),
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
