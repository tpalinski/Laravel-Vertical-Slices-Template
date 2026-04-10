<?php

declare(strict_types=1);

namespace Modules\Auth\Test\Unit\Service\User;

use DateTimeImmutable;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Domain\DTO\AuthTicket\AuthTicketDTO;
use Modules\Auth\Domain\DTO\UserCredentials\LoginDTO;
use Modules\Auth\Domain\DTO\UserCredentials\RegisterDTO;
use Modules\Auth\Domain\Exception\User\DuplicateLoginException;
use Modules\Auth\Domain\Exception\User\InvalidClientException;
use Modules\Auth\Domain\Exception\User\InvalidPasswordException;
use Modules\Auth\Domain\Exception\User\NonexistentUserException;
use Modules\Auth\Domain\Exception\User\UserNotAuthenticatedException;
use Modules\Auth\Domain\Repository\AuthTicket\AuthTicketRepositoryInterface;
use Modules\Auth\Domain\Repository\User\UserCredentialsRepository;
use Modules\Auth\Domain\Service\UserCredentials\LocalUserCredentialsService;
use Modules\Auth\Persistence\Model\UserCredentials;
use Mockery;

class LocalUserCredentialsServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /* =========================================================
     * validateCredentials
     * ========================================================= */

    public function test_validate_credentials_success_returns_ticket(): void
    {
        $userRepo = Mockery::mock(UserCredentialsRepository::class);
        $ticketRepo = Mockery::mock(AuthTicketRepositoryInterface::class);

        $creds = new UserCredentials([
            'userId' => 'user-123',
            'login' => 'john',
        ]);
        $creds->password = 'hashed-password';

        $userRepo->shouldReceive('getByField')
            ->once()
            ->with('login', 'john')
            ->andReturn($creds);

        Hash::shouldReceive('check')
            ->once()
            ->with('secret', 'hashed-password')
            ->andReturn(true);

        $ticketRepo->shouldReceive('createAuthTicket')
            ->once()
            ->with(Mockery::on(function ($dto) {
                return $dto instanceof AuthTicketDTO;
            }))
            ->andReturn('ticket-abc');

        $service = new LocalUserCredentialsService($userRepo, $ticketRepo);

        $result = $service->validateCredentials(
            new LoginDTO(
                login: 'john',
                password: 'secret',
                clientId: 'client-1'
            )
        );

        $this->assertSame('ticket-abc', $result);
    }

    public function test_validate_credentials_throws_when_user_not_found(): void
    {
        $userRepo = Mockery::mock(UserCredentialsRepository::class);
        $ticketRepo = Mockery::mock(AuthTicketRepositoryInterface::class);

        $userRepo->shouldReceive('getByField')
            ->once()
            ->with('login', 'john')
            ->andReturn(null);

        $service = new LocalUserCredentialsService($userRepo, $ticketRepo);

        $this->expectException(NonexistentUserException::class);

        $service->validateCredentials(
            new LoginDTO(
                login: 'john',
                password: 'secret',
                clientId: 'client-1'
            )
        );
    }

    public function test_validate_credentials_throws_when_password_invalid(): void
    {
        $userRepo = Mockery::mock(UserCredentialsRepository::class);
        $ticketRepo = Mockery::mock(AuthTicketRepositoryInterface::class);

        $creds = new UserCredentials([
            'userId' => 'user-123',
            'login' => 'john',
        ]);
        $creds->password = 'hashed-password';

        $userRepo->shouldReceive('getByField')
            ->once()
            ->with('login', 'john')
            ->andReturn($creds);

        Hash::shouldReceive('check')
            ->once()
            ->with('wrong-password', 'hashed-password')
            ->andReturn(false);

        $service = new LocalUserCredentialsService($userRepo, $ticketRepo);

        $this->expectException(InvalidPasswordException::class);

        $service->validateCredentials(
            new LoginDTO(
                login: 'john',
                password: 'wrong-password',
                clientId: 'client-1'
            )
        );
    }

    /* =========================================================
     * registerUser
     * ========================================================= */

    public function test_register_user_success_returns_ticket(): void
    {
        $userRepo = Mockery::mock(UserCredentialsRepository::class);
        $ticketRepo = Mockery::mock(AuthTicketRepositoryInterface::class);

        $userRepo->shouldReceive('getByField')
            ->once()
            ->with('login', 'john')
            ->andReturn(null);

        Hash::shouldReceive('make')
            ->once()
            ->with('password')
            ->andReturn('hashed-password');

        $userRepo->shouldReceive('addUser')
            ->once()
            ->with(Mockery::on(function ($user) {
                return $user instanceof UserCredentials
                    && $user->login === 'john';
            }));

        $ticketRepo->shouldReceive('createAuthTicket')
            ->once()
            ->with(Mockery::type(AuthTicketDTO::class))
            ->andReturn('ticket-xyz');

        $service = new LocalUserCredentialsService($userRepo, $ticketRepo);

        $result = $service->registerUser(
            new RegisterDTO(
                login: 'john',
                password: 'password',
                clientId: 'client-1',
                userId: 'user-123'
            )
        );

        $this->assertSame('ticket-xyz', $result);
    }

    public function test_register_user_throws_on_duplicate_login(): void
    {
        $userRepo = Mockery::mock(UserCredentialsRepository::class);
        $ticketRepo = Mockery::mock(AuthTicketRepositoryInterface::class);

        $userRepo->shouldReceive('getByField')
            ->once()
            ->with('login', 'john')
            ->andReturn(new UserCredentials());


        $service = new LocalUserCredentialsService($userRepo, $ticketRepo);

        $this->expectException(DuplicateLoginException::class);

        $service->registerUser(
            new RegisterDTO(
                login: 'john',
                password: 'password',
                clientId: 'client-1',
                userId: 'user-123'
            )
        );
    }

    /* =========================================================
     * isUserAuthenticated
     * ========================================================= */

    public function test_is_user_authenticated_success(): void
    {
        $userRepo = Mockery::mock(UserCredentialsRepository::class);
        $ticketRepo = Mockery::mock(AuthTicketRepositoryInterface::class);

        $ticketRepo->shouldReceive('getAuthTicket')
            ->once()
            ->with('ticket-123')
            ->andReturn(new AuthTicketDTO(
                userId: 'user-123',
                login: 'john',
                clientId: 'client-1',
                created: new DateTimeImmutable()
            ));

        $service = new LocalUserCredentialsService($userRepo, $ticketRepo);

        $result = $service->isUserAuthenticated('ticket-123', 'client-1');

        $this->assertSame('user-123', $result);
    }

    public function test_is_user_authenticated_throws_when_missing_ticket(): void
    {
        $userRepo = Mockery::mock(UserCredentialsRepository::class);
        $ticketRepo = Mockery::mock(AuthTicketRepositoryInterface::class);

        $ticketRepo->shouldReceive('getAuthTicket')
            ->once()
            ->with('missing')
            ->andReturn(null);

        $service = new LocalUserCredentialsService($userRepo, $ticketRepo);

        $this->expectException(UserNotAuthenticatedException::class);

        $service->isUserAuthenticated('missing', 'client-1');
    }

    public function test_is_user_authenticated_throws_when_client_mismatch(): void
    {
        $userRepo = Mockery::mock(UserCredentialsRepository::class);
        $ticketRepo = Mockery::mock(AuthTicketRepositoryInterface::class);

        $ticketRepo->shouldReceive('getAuthTicket')
            ->once()
            ->with('ticket-123')
            ->andReturn(new AuthTicketDTO(
                userId: 'user-123',
                login: 'john',
                clientId: 'wrong-client',
                created: new DateTimeImmutable()
            ));

        $service = new LocalUserCredentialsService($userRepo, $ticketRepo);

        $this->expectException(InvalidClientException::class);

        $service->isUserAuthenticated('ticket-123', 'client-1');
    }
}
