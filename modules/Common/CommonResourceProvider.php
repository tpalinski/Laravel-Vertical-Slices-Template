<?php

declare(strict_types=1);

namespace Modules\Common;

use Core\Providers\ModuleResourceProvider;

class CommonResourceProvider extends ModuleResourceProvider
{
    protected function modulePath(): string {
        return base_path('modules/Common');
    }

    protected function routePrefix(): string {
        return 'common';
    }
}