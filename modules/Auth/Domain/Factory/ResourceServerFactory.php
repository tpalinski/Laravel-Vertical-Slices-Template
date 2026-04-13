<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Factory;

use League\OAuth2\Server\ResourceServer;
use Modules\Auth\Domain\Repository\Token\AccessTokenRepository;

class ResourceServerFactory {
    public function build() {
        $repo = new AccessTokenRepository();
        $publicKeyPath = config('auth.encryption.publicKeyPath');
        $publicKeyPath = storage_path($publicKeyPath);
        return new ResourceServer($repo, 'file://' . $publicKeyPath);
    }
}
