<?php

declare(strict_types=1);

namespace Modules\Common\Providers;

use Core\Commands\MakeModuleCommand;
use Core\Commands\MakeModuleMigrationCommand;
use Core\Commands\MakeModuleModelCommand;
use Illuminate\Support\ServiceProvider;

use Modules\WorkTime\WorkTimeModuleProvider;

/**
 * CoreServiceProvider — the root of the application.
 *
 * Responsibilities:
 *   - Boot all registered Modules via their own ModuleServiceProviders
 *   - Register any cross-cutting infrastructure (macros, observers, etc.)
 *
 * New modules are added by appending to $modules below.
 * Nothing else should change in bootstrap or config/app.php.
 */
class CoreServiceProvider extends ServiceProvider
{
    /**
     * Registered module service providers.
     * Add a new module by appending its ServiceProvider class here.
     */
    protected array $modules = [
        WorkTimeModuleProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->modules as $module) {
            $this->app->register($module);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
                MakeModuleMigrationCommand::class,
                MakeModuleModelCommand::class,
            ]);
        }
    }
}
