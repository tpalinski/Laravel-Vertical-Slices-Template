<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;
use Modules\Auth\Domain\Repository\AuthTicket\AuthTicketRepositoryInterface;
use Modules\Auth\Domain\Repository\AuthTicket\CacheAuthTicketRepository;
use Modules\Auth\Domain\Service\OAuthService;
use Modules\Auth\Domain\Service\AuthServiceInterface;
use Modules\Auth\Domain\Service\UserCredentials\LocalUserCredentialsService;
use Modules\Auth\Domain\Service\UserCredentials\UserCredentialsServiceInterface;

class AuthServiceProvider extends ModuleServiceProvider
{
    protected function modulePath(): string {
        return base_path('modules/Auth');
    }

    public array $moduleBindings = [
        AuthServiceInterface::class => OAuthService::class,
        UserCredentialsServiceInterface::class => LocalUserCredentialsService::class,
        AuthTicketRepositoryInterface::class => CacheAuthTicketRepository::class,
    ];
}
