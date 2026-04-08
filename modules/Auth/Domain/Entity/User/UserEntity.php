<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entity\User;

use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity implements UserEntityInterface {

    public function __construct(
        private readonly string $userId
    ) {}

    public function getIdentifier(): string {
        return $this->userId;
    }
}
