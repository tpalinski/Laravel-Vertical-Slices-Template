<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repository\Client;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Modules\Auth\Domain\Entity\Client\ClientEntity;

class ClientRepository implements ClientRepositoryInterface {

    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface {
        return new ClientEntity($clientIdentifier);
    }

    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool {
        return true;
    }
}

