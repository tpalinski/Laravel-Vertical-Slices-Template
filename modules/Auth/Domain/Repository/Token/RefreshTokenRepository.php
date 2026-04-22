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
            'token_id' => $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
            'revoked' => false,
        ]);
    }

    public function revokeRefreshToken(string $tokenId): void {
        DB::table('refresh_tokens')->where('token_id', '=', $tokenId)->update([
            'revoked' => true,
        ]);
    }

    public function isRefreshTokenRevoked(string $tokenId): bool {
        $token = DB::table('refresh_tokens')->where('token_id', '=', $tokenId)->first();
        if ($token === null || $token->revoked) {
            return true;
        }
        return false;
    }
}
