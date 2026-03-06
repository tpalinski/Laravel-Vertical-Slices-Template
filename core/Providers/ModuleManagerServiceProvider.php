<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\ModuleManager;
use Illuminate\Support\ServiceProvider;

class ModuleManagerServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->singleton(ModuleManager::class, function () {
            return new ModuleManager();
        });
    }

    public function boot(): void {
        app(ModuleManager::class)->registerModules();
    }
}
