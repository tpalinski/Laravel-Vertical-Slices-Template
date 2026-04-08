<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repository\User;

use Core\Repository\Repository;
use Modules\Auth\Domain\Exception\User\DuplicateLoginException;
use Modules\Auth\Persistence\Model\UserCredentials;

class UserCredentialsRepository extends Repository {

    public function model(): string {
        return UserCredentials::class;
    }

    public function addUser(UserCredentials $user): UserCredentials {
        if(UserCredentials::where('login', '=', $user->login)->exists()) {
            throw new DuplicateLoginException("User with such login already exists");
        }
        return parent::create($user);
    }
}
