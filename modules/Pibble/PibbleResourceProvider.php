<?php

declare(strict_types=1);

namespace Modules\Pibble;

use Core\Providers\ModuleResourceProvider;

class PibbleResourceProvider extends ModuleResourceProvider
{
    protected function modulePath(): string {
        return base_path('modules/Pibble');
    }

    protected function routePrefix(): string {
        return 'pibble';
    }
}