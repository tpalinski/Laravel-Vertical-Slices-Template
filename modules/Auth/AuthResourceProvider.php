<?php

declare(strict_types=1);

namespace Modules\Auth;

use Core\Providers\ModuleResourceProvider;

class AuthResourceProvider extends ModuleResourceProvider
{
    protected function modulePath(): string {
        return base_path('modules/Auth');
    }

    protected function routePrefix(): string {
        return 'auth';
    }
}