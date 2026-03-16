<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Cache\CoreCache;
use Core\ModuleManager;
use Illuminate\Support\ServiceProvider;

class ModuleManagerServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager(
                new CoreCache()
            );
        });
    }

    public function boot(): void {
        $this->app->make(ModuleManager::class)->registerModules();
    }
}
