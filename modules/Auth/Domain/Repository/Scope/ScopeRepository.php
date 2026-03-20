<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repository\Scope;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Modules\Auth\Domain\Entity\Scope\ScopeEntity;

class ScopeRepository implements ScopeRepositoryInterface {

    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface {
        return new ScopeEntity($identifier);
    }

    public function finalizeScopes(array $scopes, string $grantType, ClientEntityInterface $clientEntity, ?string $userIdentifier = null, ?string $authCodeId = null): array {
        return $scopes;
    }
}
