<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Response;

use Spatie\LaravelData\Data;

class AuthTicketResponse extends Data {
    public function __construct(
        public readonly string $authTicket,
    ) {}
}
