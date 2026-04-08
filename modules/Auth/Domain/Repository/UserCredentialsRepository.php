<?php

namespace Modules\Auth\Domain\Repository;

use Core\Repository\Repository;

class UserCredentialsRepository extends Repository
{
    public function model(): string
    {
        return \Modules\Auth\Persistence\Model\UserCredentials::class;
    }
}