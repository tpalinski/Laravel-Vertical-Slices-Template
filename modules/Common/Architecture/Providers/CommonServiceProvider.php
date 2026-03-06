<?php

declare(strict_types=1);

namespace Modules\Common\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;

class CommonServiceProvider extends ModuleServiceProvider
{
    public function provides(): array {
        return [];
    }

    protected function registerBindings(): void {

    }
}
