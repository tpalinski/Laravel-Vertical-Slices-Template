<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Cache\CoreCache;
use Core\ServiceProxy\CoreProxyGenerator;
use Core\ServiceProxy\ServiceProxy;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Symfony\Contracts\Cache\CacheInterface;

abstract class ModuleServiceProvider extends ServiceProvider implements DeferrableProvider
{

    private readonly CacheInterface $cache;
    private readonly CoreProxyGenerator $generator;

    public function __construct($app)
    {
        $this->generator = new CoreProxyGenerator();
        $this->cache = new CoreCache();
        return parent::__construct($app);
    }

    /**
     * Services bound by this provider
     *
     * @var array<class-string, class-string>
     */
    public array $moduleBindings = [];

    /**
     * Path for the module
     */
    protected abstract function modulePath(): string;


    /**
     * Services provided by this provider (required for deferred loading).
     *
     * @return array<class-string>
     */
    public function provides(): array {
        $provides = array_keys($this->moduleBindings);
        dd($provides);
        return $provides;
    }

    /**
     * Register domain bindings.
     */
    public function register(): void {
        $flags = $this->getFeatureFlags();
        foreach ($this->moduleBindings as $interface => $concrete) {
            $this->bindWithProxy($interface, $concrete, $flags);
        }
    }

    /**
     * Boot domain logic (if needed).
     */
    public function boot(): void {}

    //
    // Config and feature flag related stuff
    //

    /**
     * Binds the concrete implementation via proxy, used for checking feature flags
     */
    protected function bindWithProxy(string $interface, string $concrete, array $flags) {
        $this->app->bind($interface, function ($app) use ($concrete, $flags, $interface) {
            $service = $app->make($concrete);
            $proxy = new ServiceProxy($flags, $service);
            $interfaceProxy = $this->generator->generateProxy($interface, $proxy);
            return $interfaceProxy;
        });
    }

    protected function getModuleName(): string {
        $frags = explode('/', trim($this->modulePath(), '/'));
        $name = end($frags);
        return ucfirst($name);
    }

    protected function cacheKey(): string {
        return 'modules.' . $this->getModuleName();
    }

    /**
     * Load module manifest (module.json)
     */
    protected function manifest(): array {
        return $this->cache->get(
            $this->cacheKey() . '.manifest',
            function () {
                $manifestPath = rtrim($this->modulePath(), '/')
                    . '/module.json';

                if (!is_file($manifestPath)) {
                    return [];
                }

                return json_decode(
                    file_get_contents($manifestPath),
                    true
                ) ?? [];
            }
        );
    }

    protected function getFeatureFlags(): array {
        $manifest = $this->manifest();
        return $manifest['featureFlags'];
    }

}
