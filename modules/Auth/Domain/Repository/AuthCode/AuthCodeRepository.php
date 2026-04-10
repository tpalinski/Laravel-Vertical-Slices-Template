<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repository\AuthCode;

use Illuminate\Support\Facades\Cache;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Modules\Auth\Domain\Entity\AuthCode\AuthCodeEntity;
use Symfony\Component\Uid\UuidV4;

class AuthCodeRepository implements AuthCodeRepositoryInterface {


    const int TOKEN_LENGTH = 64;
    const string CACHE_KEY = 'auth.codes';

    private function getCacheTTL(): int {
        return 600;
    }

    private function getPersistanceCacheKey(string $codeId): string {
        return AuthCodeRepository::CACHE_KEY . '.persisted.' . $codeId;
    }

    private function getRevokedCacheKey(string $codeId): string {
        return AuthCodeRepository::CACHE_KEY . '.revoked.' . $codeId;
    }

    public function getNewAuthCode(): AuthCodeEntityInterface {
        $code = new AuthCodeEntity();
        $identifier = UuidV4::v4()->toString();
        $identifier = hash('sha256', $identifier);
        $identifier = substr($identifier, 0, AuthCodeRepository::TOKEN_LENGTH);
        $code->setIdentifier($identifier);
        return $code;
    }

    public function revokeAuthCode(string $codeId): void {
        $key = $this->getRevokedCacheKey($codeId);
        Cache::put($key, true, $this->getCacheTTL());
    }

    public function isAuthCodeRevoked(string $codeId): bool {
        $key = $this->getRevokedCacheKey($codeId);
        return Cache::has($key);
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void {
        $key = $this->getPersistanceCacheKey($authCodeEntity->getIdentifier());
        Cache::put($key, $authCodeEntity->getIdentifier(), $this->getCacheTTL());
    }
}
