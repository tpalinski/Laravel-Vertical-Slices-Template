<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Request;

use Spatie\LaravelData\Data;

class LoginRequest extends Data {
    public function __construct(
        public readonly string $login,
        public readonly string $password,
        public readonly string $clientId,
    ) {}
}
