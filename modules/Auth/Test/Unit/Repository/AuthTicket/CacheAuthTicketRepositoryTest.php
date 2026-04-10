<?php

declare(strict_types=1);

namespace Modules\Auth\Test\Unit\Repository\AuthTicket;

use Illuminate\Support\Facades\Cache;
use Modules\Auth\Domain\DTO\AuthTicket\AuthTicketDTO;
use Modules\Auth\Domain\Repository\AuthTicket\CacheAuthTicketRepository;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\TestCase;

class CacheAuthTicketRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /* =========================================================
     * createAuthTicket
     * ========================================================= */

    public function test_create_auth_ticket_stores_in_cache_and_returns_key(): void
    {
        $repo = new CacheAuthTicketRepository();

        $dto = new AuthTicketDTO(
            userId: 'user-123',
            login: 'john',
            clientId: 'client-1',
            created: new DateTimeImmutable()
        );

        $key = $repo->createAuthTicket($dto);

        // 1. key structure is correct
        $this->assertStringStartsWith('auth.tickets.', $key);

        // 2. value is actually stored in cache
        $stored = Cache::get($key);

        $this->assertNotNull($stored);

        $this->assertEquals([
            'userId' => 'user-123',
            'login' => 'john',
            'clientId' => 'client-1',
            'created' => $dto->toArray()['created'],
        ], $stored);
    }

    /* =========================================================
     * getAuthTicket - missing
     * ========================================================= */

    public function test_get_auth_ticket_returns_null_when_missing(): void
    {
        $repo = new CacheAuthTicketRepository();

        Cache::shouldReceive('get')
            ->once()
            ->with('auth.tickets.missing')
            ->andReturn(null);

        $result = $repo->getAuthTicket('missing');

        $this->assertNull($result);
    }

    /* =========================================================
     * getAuthTicket - success
     * ========================================================= */

    public function test_get_auth_ticket_returns_dto_when_found(): void
    {
        $repo = new CacheAuthTicketRepository();

        $stored = [
            'userId' => 'user-123',
            'login' => 'john',
            'clientId' => 'client-1',
            'created' => (new DateTimeImmutable())->format(DATE_ATOM),
        ];

        Cache::shouldReceive('get')
            ->once()
            ->with('auth.tickets.somekey')
            ->andReturn($stored);

        $result = $repo->getAuthTicket('somekey');

        $this->assertInstanceOf(AuthTicketDTO::class, $result);

        $this->assertSame('user-123', $result->userId);
        $this->assertSame('john', $result->login);
        $this->assertSame('client-1', $result->clientId);

        // important: ensure created is properly hydrated
        $this->assertInstanceOf(DateTimeImmutable::class, $result->created);
    }
}
