<?php

declare(strict_types=1);

namespace Modules\Pibble\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;
use Modules\Pibble\Architecture\Service\PibbleService;
use Modules\Pibble\Domain\Service\PibbleServiceInterface;

class PibbleServiceProvider extends ModuleServiceProvider
{
    protected function modulePath(): string {
        return base_path('modules/Pibble');
    }

    public array $moduleBindings = [
        PibbleServiceInterface::class => PibbleService::class,
    ];
}
