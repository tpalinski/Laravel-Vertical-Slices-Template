<?php

declare(strict_types=1);

namespace Core\Enum;

enum Environment: string {
    case PRODUCTION = "production";
    case STAGING = "staging";
    case DEVELOPMENT = "development";
    case TESTING = "testing";
    case LOCAL = "local";

    public function asInt() : int {
        return match ($this) {
            Environment::TESTING => 0,
            Environment::LOCAL => 1,
            Environment::DEVELOPMENT => 2,
            Environment::STAGING => 3,
            Environment::PRODUCTION => 4,
        };
    }
}
