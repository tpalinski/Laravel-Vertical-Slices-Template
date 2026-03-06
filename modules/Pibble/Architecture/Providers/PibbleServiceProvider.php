<?php

declare(strict_types=1);

namespace Modules\Pibble\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;
use Modules\Pibble\Architecture\Service\PibbleService;
use Modules\Pibble\Domain\Service\PibbleServiceInterface;

class PibbleServiceProvider extends ModuleServiceProvider
{
    public function provides(): array {
        return [PibbleServiceInterface::class];
    }

    protected function registerBindings(): void {
        $this->app->bind(PibbleServiceInterface::class, PibbleService::class);
    }
}
