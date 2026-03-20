<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entity\Scope;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

class ScopeEntity implements ScopeEntityInterface {

    public function __construct(
        private string $identifier,
    ) {}

    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function jsonSerialize(): mixed {
        return $this->identifier;
    }
}
