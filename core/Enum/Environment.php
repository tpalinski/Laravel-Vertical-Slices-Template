<?php

declare(strict_types=1);

namespace Core\Enum;

enum Environment: string {
    case PRODUCTION = "production";
    case STAGING = "staging";
    case DEVELOPMENT = "development";
    case LOCAL = "local";

    public function asInt() : int {
        return match ($this) {
            Environment::LOCAL => 0,
            Environment::DEVELOPMENT => 1,
            Environment::STAGING => 2,
            Environment::PRODUCTION => 3,
        };
    }
}
