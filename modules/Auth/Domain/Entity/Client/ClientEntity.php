<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entity\Client;

use League\OAuth2\Server\Entities\ClientEntityInterface;

class ClientEntity implements ClientEntityInterface {

    private string $clientName;
    private string $identifier;
    private string $redirectUri;
    private bool $isConfidential;

    public function __construct(string $clientIdentifier) {
        $this->identifier = $clientIdentifier;
        $config = config("auth.clients.{$clientIdentifier}", "");
        $this->clientName = $config['name'] ?? "";
        $this->isConfidential = $config['confidential'] ?? true;
        $this->redirectUri = $config['redirectUri'] ?? "";
    }

    public function getName(): string {
        return $this->clientName;
    }

    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function getRedirectUri(): string|array {
        return $this->redirectUri;
    }

    public function isConfidential(): bool {
        return $this->isConfidential;
    }
}
