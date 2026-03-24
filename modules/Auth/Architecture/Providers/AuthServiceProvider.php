<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;
use Modules\Auth\Domain\Service\OAuthService;
use Modules\Auth\Domain\Service\AuthServiceInterface;

class AuthServiceProvider extends ModuleServiceProvider
{
    protected function modulePath(): string {
        return base_path('modules/Auth');
    }

    public array $moduleBindings = [
        AuthServiceInterface::class => OAuthService::class,
    ];
}
