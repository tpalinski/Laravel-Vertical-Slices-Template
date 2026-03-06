<?php

declare(strict_types=1);

namespace Core\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

/**
 * ModuleServiceProvider — base class for every Module's provider.
 *
 * Each module extends this and overrides the hook methods it needs.
 * This keeps individual module providers clean and focused.
 *
 * Module providers are responsible for:
 *   - Registering module routes
 *   - Publishing / merging module config
 *   - Loading module migrations and views
 *   - Registering module-specific bindings / singletons
 */
abstract class ModuleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Override in the module to return the module's root path.
     * Typically: dirname(__DIR__)
     */
    abstract protected function modulePath(): string;

    /**
     * Override to return the module's route namespace prefix (e.g. 'auth').
     */
    abstract protected function routePrefix(): string;

    public function register(): void
    {
        $this->mergeModuleConfig();
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleMigrations();
    }

    // -------------------------------------------------------------------------
    // Protected helpers — override as needed in the concrete module provider
    // -------------------------------------------------------------------------

    protected function mergeModuleConfig(): void {
        $configPath = $this->modulePath() . '/config/' . $this->routePrefix() . '.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, $this->routePrefix());
        }
    }

    protected function loadModuleRoutes(): void {
        $routesPath = $this->modulePath() . 'Rest/routes';

        if (file_exists($routesPath . '/api.php')) {
            $this->loadRoutesFrom($routesPath . '/api.php');
        }
    }

    protected function loadModuleMigrations(): void {
        $migrationsPath = $this->modulePath() . '/persistence/migrations';

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }
}
