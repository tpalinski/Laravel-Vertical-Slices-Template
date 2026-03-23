<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repository\Token;

use Illuminate\Support\Facades\DB;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Modules\Auth\Domain\Entity\Token\RefreshTokenEntity;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface {

    public function getNewRefreshToken(): ?RefreshTokenEntityInterface {
        return new RefreshTokenEntity();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void {
        DB::table('refresh_tokens')->insert([
            $refreshTokenEntity->getIdentifier(),
            $refreshTokenEntity,
        ]);
    }

    public function revokeRefreshToken(string $tokenId): void {
        throw new \Exception('Not implemented');
    }

    public function isRefreshTokenRevoked(string $tokenId): bool {
        throw new \Exception('Not implemented');
    }
}
