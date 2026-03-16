<?php

declare(strict_types=1);

namespace Core;

use Core\Enum\Environment;
use Symfony\Contracts\Cache\CacheInterface;

class ModuleManager {

    const string MANIFEST_CACHE_KEY = 'manifests';
    const string BOOT_ORDER_CACHE_KEY = 'boot.order';

    public function __construct(
        private readonly CacheInterface $cache,
    ) {}

    public function registerModules(): void {
        $modules = $this->cachedModules();
        $sorted = $this->cachedBootOrder();
        /*
        if (count($sorted) !== count($modules)) {
            throw new \RuntimeException("Circular module dependency detected");
        }
         */
        // TODO - register them according to sorting
        foreach ($modules as $module) {
            $this->registerModule($module);
        }
    }

    protected function cachedModules(): array {
        return $this->cache->get(
            ModuleManager::MANIFEST_CACHE_KEY . config('app.env'),
            fn () => $this->discoverModules()
        );
    }

    protected function cachedBootOrder(): array {
        return $this->cache->get(
            ModuleManager::BOOT_ORDER_CACHE_KEY . config('app.env'),
            fn () => $this->sortModulesByDependencies(
                $this->cachedModules()
            )
        );
    }

    protected function discoverModules(): array {
        $modules = [];

        foreach (glob(base_path('modules/*/module.json')) as $manifest) {

            $environment = Environment::from(config('app.env', 'production'));
            $config = json_decode(
                file_get_contents($manifest),
                true
            );

            if (! ($config['enabled'] ?? true)) {
                continue;
            }

            $moduleEnv = Environment::from(($config['environment'] ?? 'local'));
            if ($environment->asInt() > $moduleEnv->asInt()) {
                continue;
            }

            $modules[] = [
                'path' => dirname($manifest),
                'config' => $config
            ];
        }

        return $modules;
    }

    protected function registerModule(array $module): void
    {
        $config = $module['config'];
        $resourceProvider = $config['resourceProvider'];
        $serviceProvider = $config['serviceProvider'];

        if (class_exists($resourceProvider)) {
            app()->register($resourceProvider);
        }

        if (class_exists($serviceProvider)) {
            app()->register($serviceProvider);
        }
    }

    /**
     * Topologically sort module dependencies
     */
    private function sortModulesByDependencies(array $modules): array
    {
        // Build graph
        $graph = [];
        $inDegree = [];

        foreach ($modules as $name => $module) {
            $deps = $module['config']['dependencies'] ?? [];

            $graph[$name] = $deps;
            $inDegree[$name] = $inDegree[$name] ?? 0;

            foreach ($deps as $dep) {
                $inDegree[$dep] = ($inDegree[$dep] ?? 0) + 1;
            }
        }

        // Queue modules with no dependencies
        $queue = new \SplQueue();

        foreach ($inDegree as $node => $degree) {
            if ($degree === 0) {
                $queue->enqueue($node);
            }
        }

        $sorted = [];

        while (! $queue->isEmpty()) {

            $node = $queue->dequeue();
            $sorted[] = $node;

            foreach ($graph[$node] ?? [] as $dep) {

                $inDegree[$dep]--;

                if ($inDegree[$dep] === 0) {
                    $queue->enqueue($dep);
                }
            }
        }

        return $sorted;
    }
}
