<?php

declare(strict_types=1);

namespace Modules\Pibble\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;

class PibbleServiceProvider extends ModuleServiceProvider
{
    public function provides(): array {
        return [];
    }

    protected function registerBindings(): void {

    }
}