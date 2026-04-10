<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Service\UserCredentials;

use Modules\Auth\Domain\DTO\UserCredentials\LoginDTO;
use Modules\Auth\Domain\DTO\UserCredentials\RegisterDTO;
use Modules\Auth\Domain\Exception\User\DuplicateLoginException;
use Modules\Auth\Domain\Exception\User\InvalidClientException;
use Modules\Auth\Domain\Exception\User\InvalidPasswordException;
use Modules\Auth\Domain\Exception\User\NonexistentUserException;
use Modules\Auth\Domain\Exception\User\UserNotAuthenticatedException;

interface UserCredentialsServiceInterface {
    /**
     * Validate the provided credentials against persisted user credentials and issues auth ticket
     *
     * @param LoginDTO - data of the user to be logged in
     *
     * @throws NonexistentUserException when no user with provided login is present
     * @throws InvalidPasswordException when the password does not match
     *
     * @return string $authTicket - ticket to be used further with OAuth flow
     */
    public function validateCredentials(LoginDTO $data): string;

    /**
     * Adds user credentials to the DB
     *
     * @param RegisterDTO $data - data of the user to be registered
     *
     * @throws DuplicateLoginException when such login already exists in the DB
     *
     * @return string $authTicket - ticket to be used further with OAuth flow
     */
    public function registerUser(RegisterDTO $data);

    /**
     * Check whether user has authenticated
     *
     * @param string $authTicket - ticket provided from previous authorization
     * @param string $clientId - client, for which the authentication check is ran
     *
     * @throws UserNotAuthenticatedException when user has not authenticated
     * @throws InvalidClientException when the provided client does not match the original client
     *
     * @return string $userId - user id in form of "prefix:id", such as "user:3"
     */
    public function isUserAuthenticated(string $authTicket, string $clientId): string;
}
