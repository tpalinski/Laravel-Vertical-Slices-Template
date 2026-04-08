<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Service\UserCredentials;

use Modules\Auth\Domain\Exception\User\DuplicateLoginException;
use Modules\Auth\Domain\Exception\User\InvalidPasswordException;
use Modules\Auth\Domain\Exception\User\NonexistentUserException;

interface UserCredentialsServiceInterface {
    /**
     * Validate the provided credentials against persisted user credentials
     *
     * @param string $login - login of the user
     * @param string $password - password of the user in plaintext form
     *
     * @throws NonexistentUserException when no user with provided login is present
     * @throws InvalidPasswordException when the password does not match
     *
     * @return string $userId - id of the user to be used in subsequent auth requests, in form of "prefix:id", for instance: "user:3"
     */
    public function validateCredentials(string $login, string $password): string;

    /**
     * Adds user credentials to the DB
     *
     * @param string $userId - id of the user from external modules, to be retrieved later in form of "prefix:id", for instance: "user:3"
     * @param string $password - password of the user in plaintext form
     * @param string $login - login of the user, must be unique
     *
     * @throws DuplicateLoginException when such login already exists in the DB
     */
    public function registerUser(string $userId, string $password, string $login);
}
