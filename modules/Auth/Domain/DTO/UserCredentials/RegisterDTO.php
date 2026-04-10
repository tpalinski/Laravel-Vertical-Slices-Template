<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\DTO\UserCredentials;

use Spatie\LaravelData\Data;

class RegisterDTO extends Data {
    public function __construct(
        public readonly string $login,
        public readonly string $password,
        public readonly string $clientId,
        public readonly string $userId,
    ) {}
}
