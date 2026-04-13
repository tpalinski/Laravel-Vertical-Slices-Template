<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Service;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Modules\Auth\Domain\Entity\User\UserEntity;
use Modules\Auth\Domain\Exception\Oauth\RequestValidationException;
use Modules\Auth\Domain\Factory\OAuthServerFactory;
use Modules\Auth\Domain\Service\UserCredentials\UserCredentialsServiceInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class OAuthService implements AuthServiceInterface {

    private AuthorizationServer $authServer;
    private UserCredentialsServiceInterface $userCredentialsService;

    public function __construct(
        OAuthServerFactory $factory,
        UserCredentialsServiceInterface $userCredentialsService,
    ) {
        $this->userCredentialsService = $userCredentialsService;
        $this->authServer = $factory->build();
    }

    public function authorize(string $authTicket, string $clientId, ServerRequestInterface $request, Response $response): ResponseInterface {
        try {
            $authRequest = $this->authServer->validateAuthorizationRequest($request);
        } catch (OAuthServerException $e) {
            throw new RequestValidationException($e->getMessage());
        }
        $userId = $this->userCredentialsService->isUserAuthenticated($authTicket, $clientId);
        $authRequest->setUser(new UserEntity($userId));
        $authRequest->setAuthorizationApproved(True);
        return $this->authServer->completeAuthorizationRequest($authRequest, $response);
    }

    public function issueToken(ServerRequestInterface $request, Response $response): ResponseInterface {
        return $this->authServer->respondToAccessTokenRequest($request, $response);
    }
}
