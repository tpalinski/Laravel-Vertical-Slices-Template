<?php

declare(strict_types=1);

namespace Core\Providers;

use Illuminate\Support\ServiceProvider;

abstract class ModuleResourceProvider extends ServiceProvider
{
    private array $manifest = [];

    /**
     * Module root path
     */
    abstract protected function modulePath(): string;

    /**
     * Route prefix / config namespace
     */
    abstract protected function routePrefix(): string;

    public function register(): void {
        $this->mergeModuleConfig();
    }

    public function boot(): void {
        $this->loadModuleRoutes();
        $this->loadModuleMigrations();
    }

    // ------------------------------------------------------------

    protected function loadManifest(): void {
        $manifestPath = rtrim($this->modulePath(), '/')
            . '/module.json';

        if (!is_file($manifestPath)) {
            return;
        }

        $this->manifest = json_decode(
            file_get_contents($manifestPath),
            true
        ) ?? [];
    }

    protected function mergeModuleConfig(): void {
        $configPath = rtrim($this->modulePath(), '/')
            . '/config/'
            . $this->routePrefix()
            . '.php';

        if (is_file($configPath)) {
            $this->mergeConfigFrom(
                $configPath,
                $this->routePrefix()
            );
        }
    }

    protected function loadModuleRoutes(): void {
        $routesPath = rtrim($this->modulePath(), '/')
            . '/Rest/routes/api.php';

        if (is_file($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }

    protected function loadModuleMigrations(): void {
        $migrationsPath = rtrim($this->modulePath(), '/')
            . '/persistence/migrations';

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    protected function getConfig(): array {
        if (!$this->manifest) {
            $this->loadManifest();
        }
        return $this->manifest;
    }
}
