<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repository\AuthTicket;

use Modules\Auth\Domain\DTO\AuthTicket\AuthTicketDTO;

interface AuthTicketRepositoryInterface {

    public function createAuthTicket(AuthTicketDTO $ticket): string;

    public function getAuthTicket(string $ticket): ?AuthTicketDTO;
}
