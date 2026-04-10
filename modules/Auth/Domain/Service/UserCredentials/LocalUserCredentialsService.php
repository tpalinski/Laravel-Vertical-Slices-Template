<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Service\UserCredentials;

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
use Modules\Auth\Persistence\Model\UserCredentials;

class LocalUserCredentialsService implements UserCredentialsServiceInterface {

    public function __construct(
        private readonly UserCredentialsRepository $repository,
        private readonly AuthTicketRepositoryInterface $ticketRepository,
    ) {}

    public function validateCredentials(LoginDTO $data): string {
        $creds = $this->repository->getByField('login', $data->login);
        if($creds === null) {
            throw new NonexistentUserException("No such account exists");
        }
        if (!Hash::check($data->password, $creds->password)) {
            throw new InvalidPasswordException("Passwords do not match");
        }
        $ticketContent = AuthTicketDTO::create($creds->userId, $data->login, $data->clientId);
        $ticket = $this->ticketRepository->createAuthTicket($ticketContent);
        return $ticket;
    }

    public function registerUser(RegisterDTO $data): string {
        if($this->repository->getByField('login', $data->login) !== null) {
            throw new DuplicateLoginException("User with such login already exists");
        }
        $hashedPassword = Hash::make($data->password);
        $creds = new UserCredentials([
            'userId' => $data->userId,
            'login' => $data->login,
        ]);
        $creds->password = $hashedPassword;
        $this->repository->addUser($creds);
        $ticketContent = AuthTicketDTO::create($data->userId, $data->login, $data->clientId);
        $ticket = $this->ticketRepository->createAuthTicket($ticketContent);
        return $ticket;
    }

    public function isUserAuthenticated(string $authTicket, string $clientId): string {
        $content = $this->ticketRepository->getAuthTicket($authTicket);
        if ($content===null) {
            throw new UserNotAuthenticatedException("User not authenticated");
        }
        if ($content->clientId !== $clientId) {
            throw new InvalidClientException("The client application does not match");
        }
        return $content->userId;
    }
}
