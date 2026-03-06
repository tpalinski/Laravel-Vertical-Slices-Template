<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\ModuleManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class ModuleManagerServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager(
                $app->make(CacheRepository::class)
            );
        });
    }

    public function boot(): void {
        $this->app->make(ModuleManager::class)->registerModules();
    }
}
