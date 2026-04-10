<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repository\AuthTicket;

use Illuminate\Support\Facades\Cache;
use Modules\Auth\Domain\DTO\AuthTicket\AuthTicketDTO;

class CacheAuthTicketRepository implements AuthTicketRepositoryInterface {

    const string CACHE_KEY = 'auth.tickets';

    private function getCacheKey(string $ticket): string {
        return CacheAuthTicketRepository::CACHE_KEY . '.' . $ticket;
    }

    private function generateHashedTicket(AuthTicketDTO $ticket): string {
        $hash = hash('sha256', $ticket->toJson());
        return substr($hash, 0, 32);
    }

    public function getAuthTicket(string $ticket): ?AuthTicketDTO {
        $content = Cache::get($this->getCacheKey($ticket));
        if ($content === null) {
            return null;
        }
        return AuthTicketDTO::from($content);
    }

    public function createAuthTicket(AuthTicketDTO $ticket): string {
        $hashed = $this->generateHashedTicket($ticket);
        $key = $this->getCacheKey($hashed);
        $content = $ticket->toArray();
        Cache::put($key, $content, 60*5);
        return $key;
    }
}
