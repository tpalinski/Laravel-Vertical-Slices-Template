<?php

declare(strict_types=1);

namespace Modules\WorkTime;

use Core\Providers\ModuleServiceProvider;

class WorkTimeModuleProvider extends ModuleServiceProvider
{
    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }

    protected function routePrefix(): string
    {
        return 'worktime';
    }
}
