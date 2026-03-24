<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Service;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthServiceInterface {
    /**
     * Respond to PSR7 token request
     * @param ServerRequestInterface $request - PSR7 request for token issuing
     * @param Response $response - PSR7 response, most likely an empty one
     *
     * @return ResponseInterface $response - response with oauth token
     */
    public function issueToken(ServerRequestInterface $request, Response $response): ResponseInterface;
}
