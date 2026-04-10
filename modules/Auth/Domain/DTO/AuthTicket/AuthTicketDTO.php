<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\DTO\AuthTicket;

use DateTimeImmutable;
use Spatie\LaravelData\Data;

class AuthTicketDTO extends Data
{
    public function __construct(
        public readonly string $userId,
        public readonly string $login,
        public readonly string $clientId,
        public readonly DateTimeImmutable $created,
    ) {}

    public static function create(
        string $userId,
        string $login,
        string $clientId,
    ): self {
        return new self(
            userId: $userId,
            login: $login,
            clientId: $clientId,
            created: new DateTimeImmutable(),
        );
    }
}
