<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Factory;

use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Modules\Auth\Domain\Repository\AuthCode\AuthCodeRepository;
use Modules\Auth\Domain\Repository\Client\ClientRepository;
use Modules\Auth\Domain\Repository\Scope\ScopeRepository;
use Modules\Auth\Domain\Repository\Token\AccessTokenRepository;
use Modules\Auth\Domain\Repository\Token\RefreshTokenRepository;

class OAuthServerFactory {
    public function build() {
        $privateKeyPath = config('auth.encryption.privateKeyPath');
        $encryptionKey = config('auth.encryption.key');
        $privateKeyPath = storage_path($privateKeyPath);
        $authServer = new AuthorizationServer(
            new ClientRepository(),
            new AccessTokenRepository(),
            new ScopeRepository(),
            'file://' . $privateKeyPath,
            $encryptionKey,
        );

        $authServer->enableGrantType(
            new ClientCredentialsGrant(),
            new DateInterval('PT1H'),
        );

        $refreshGrant = new RefreshTokenGrant(new RefreshTokenRepository());
        $refreshGrant->setRefreshTokenTTL(new DateInterval('P30D'));
        $authServer->enableGrantType(
            $refreshGrant,
            new DateInterval('PT1H'),
        );
        $authGrant = new AuthCodeGrant(
            new AuthCodeRepository(),
            new RefreshTokenRepository(),
            new DateInterval('PT10M'),
        );
        $authGrant->setRefreshTokenTTL(new DateInterval('P30D'));
        $authServer->enableGrantType(
            $authGrant,
            new DateInterval('PT1H'),
        );
        return $authServer;
    }
}
