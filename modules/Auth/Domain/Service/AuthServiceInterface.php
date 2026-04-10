<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Service;

use Modules\Auth\Domain\Exception\User\InvalidClientException;
use Modules\Auth\Domain\Exception\User\UserNotAuthenticatedException;
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

    /**
     * Respond to authorization code request
     * @param string $login - user login
     * @param string $password - user password in plaintext
     * @param ServerRequestInterface $request - PSR7 request for token issuing
     * @param Response $response - PSR7 response, most likely an empty one
     *
     * @throws UserNotAuthenticatedException when user is not authenticated
     * @throws InvalidClientException when the client ids mismatch with authentication record
     *
     * @return ResponseInterface $response - response with oauth token
     */
    public function authorize(string $authTicket, string $clientId, ServerRequestInterface $request, Response $response): ResponseInterface;
}
