<?php

declare(strict_types=1);

namespace Modules\Common\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;

class CommonServiceProvider extends ModuleServiceProvider
{
    protected function modulePath(): string {
        return base_path('modules/Common');
    }

    public array $moduleBindings = [];
}
