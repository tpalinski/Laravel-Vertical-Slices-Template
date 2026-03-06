<?php

declare(strict_types=1);

namespace Core\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Services provided by this provider (required for deferred loading).
     *
     * @return array<class-string>
     */
    public function provides(): array {
        return [];
    }

    /**
     * Register domain bindings.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    /**
     * Boot domain logic (if needed).
     */
    public function boot(): void {
    }

    /**
     * Override in modules to bind domain services.
     */
    protected function registerBindings(): void {
    }
}
