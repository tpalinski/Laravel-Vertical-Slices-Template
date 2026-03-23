<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repository\Token;

use Illuminate\Support\Facades\Cache;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Modules\Auth\Domain\Entity\Token\AccessTokenEntity;

class AccessTokenRepository implements AccessTokenRepositoryInterface {

    private string $cacheKey = 'auth.tokens.access.revoked.';

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, ?string $userIdentifier = null): AccessTokenEntityInterface {
        return new AccessTokenEntity($clientEntity, $scopes, $userIdentifier);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void {
        return;
    }

    public function revokeAccessToken(string $tokenId): void {
        $key = $this->cacheKey . $tokenId;
        $ttl = config('auth.tokens.ttl', 60);
        Cache::put($key, true, $ttl);
    }

    public function isAccessTokenRevoked(string $tokenId): bool {
        $key = $this->cacheKey . $tokenId;
        return Cache::has($key);
    }

}
