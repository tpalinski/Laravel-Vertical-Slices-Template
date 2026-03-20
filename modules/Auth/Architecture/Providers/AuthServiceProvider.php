<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    protected function modulePath(): string {
        return base_path('modules/Auth');
    }

    public array $moduleBindings = [];
}