<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entity\Token;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface {
    use AccessTokenTrait;
    use TokenEntityTrait;
    use EntityTrait;

    public function __construct(ClientEntityInterface $client, array $scopes, ?string $userIdentifier) {
        $this->client = $client;
        $this->scopes = $scopes;
        $this->userIdentifier = $userIdentifier;
    }
}
