<?php

declare(strict_types=1);

namespace Core\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse {
    public function handle(Request $request, Closure $next): Response {
        $request->headers->set('Accept', 'application/json');
        /** @var Response $response */
        $response = $next($request);
        return $response;
    }
}
