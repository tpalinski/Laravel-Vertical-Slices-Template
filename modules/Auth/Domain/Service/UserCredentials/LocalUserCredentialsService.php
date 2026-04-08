<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Service\UserCredentials;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Domain\Exception\User\InvalidPasswordException;
use Modules\Auth\Domain\Exception\User\NonexistentUserException;
use Modules\Auth\Domain\Repository\User\UserCredentialsRepository;
use Modules\Auth\Persistence\Model\UserCredentials;

class LocalUserCredentialsService implements UserCredentialsServiceInterface {

    public function __construct(
        private readonly UserCredentialsRepository $repository,
    ) {}

    public function validateCredentials(string $login, string $password): string {
        $creds = $this->repository->getByField('login', $login);
        if($creds === null) {
            throw new NonexistentUserException("No such account exists");
        }
        if (!Hash::check($password, $creds->password)) {
            throw new InvalidPasswordException("Passwords do not match");
        }
        return $creds->userId;
    }

    public function registerUser(string $userId, string $password, string $login) {
        $hashedPassword = Hash::make($password);
        $creds = new UserCredentials([
            'userId' => $userId,
            'login' => $login,
            'password' => $hashedPassword,
        ]);
        $this->repository->addUser($creds);
    }
}
