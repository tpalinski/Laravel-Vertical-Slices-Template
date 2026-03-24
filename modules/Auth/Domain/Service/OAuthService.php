<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Service;

use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Modules\Auth\Domain\Repository\Client\ClientRepository;
use Modules\Auth\Domain\Repository\Scope\ScopeRepository;
use Modules\Auth\Domain\Repository\Token\AccessTokenRepository;
use Modules\Auth\Domain\Repository\Token\RefreshTokenRepository;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class OAuthService implements AuthServiceInterface {

    private AuthorizationServer $authServer;

    public function __construct(
        ClientRepository $clientRepository,
        ScopeRepository $scopeRepository,
        AccessTokenRepository $accessTokenRepository,
        RefreshTokenRepository $refreshTokenRepository,
    ) {
        $privateKeyPath = config('auth.encryption.privateKeyPath');
        $encryptionKey = config('auth.encryption.key');
        $privateKeyPath = storage_path($privateKeyPath);
        $this->authServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            'file://' . $privateKeyPath,
            $encryptionKey,
        );

        $this->authServer->enableGrantType(
            new ClientCredentialsGrant(),
            new DateInterval('PT1H'),
        );

        $refreshGrant = new RefreshTokenGrant($refreshTokenRepository);
        $refreshGrant->setRefreshTokenTTL(new DateInterval('P30D'));
        $this->authServer->enableGrantType(
            $refreshGrant,
            new DateInterval('PT1H'),
        );
    }

    public function issueToken(ServerRequestInterface $request, Response $response): ResponseInterface {
        return $this->authServer->respondToAccessTokenRequest($request, $response);
    }
}
