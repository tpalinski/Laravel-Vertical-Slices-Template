<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\ServiceProxy\ServiceProxy;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use ReflectionUnionType;

abstract class ModuleServiceProvider extends ServiceProvider implements DeferrableProvider
{

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
            $interfaceProxy = $this->generateProxy($interface);
            return new $interfaceProxy($proxy);
        });
    }

    protected function cacheKey(): string {
        return 'framework.modules.' . $this->modulePath();
    }

    /**
     * Load module manifest (module.json)
     */
    protected function manifest(): array {
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

    protected function getFeatureFlags(): array {
        $manifest = $this->manifest();
        return $manifest['features'];
    }

    protected function generateProxy(string $interface): string {
        $reflection = new ReflectionClass($interface);
        $methods = '';

        foreach ($reflection->getMethods() as $method) {

            $params = [];
            $args = [];

            foreach ($method->getParameters() as $param) {

                $paramCode = '';

                if ($param->hasType()) {
                    $type = $param->getType();
                    $nullable = $type->allowsNull() && !$type instanceof ReflectionUnionType ? '?' : '';

                    if ($type instanceof ReflectionUnionType) {
                        $types = [];
                        foreach ($type->getTypes() as $t) {
                            $types[] = ($t->allowsNull() ? '?' : '') . $t->getName();
                        }
                        $paramCode .= implode('|', $types) . ' ';
                    } else {
                        $paramCode .= $nullable . $type->getName() . ' ';
                    }
                }

                // Reference
                if ($param->isPassedByReference()) {
                    $paramCode .= '&';
                }

                // Variadic
                if ($param->isVariadic()) {
                    $paramCode .= '...';
                }

                // Parameter name
                $paramCode .= '$' . $param->getName();

                // Default value (only if not variadic)
                if ($param->isDefaultValueAvailable() && !$param->isVariadic()) {
                    $paramCode .= ' = ' . var_export($param->getDefaultValue(), true);
                }

                $params[] = $paramCode;

                // Arguments for forwarding
                $args[] = ($param->isVariadic() ? '...' : '') . '$' . $param->getName();
            }

            // Return type
            $returnType = '';
            $returnTypePrefix = '';

            if ($method->hasReturnType()) {
                $type = $method->getReturnType();
                $nullable = $type->allowsNull() && !$type instanceof ReflectionUnionType ? '?' : '';

                if ($type instanceof ReflectionUnionType) {
                    $types = [];
                    foreach ($type->getTypes() as $t) {
                        $types[] = ($t->allowsNull() ? '?' : '') . $t->getName();
                    }
                    $returnType = ': ' . implode('|', $types);
                } else {
                    $returnType = ': ' . $nullable . $type->getName();
                }
            }

            // Handle void return type separately
            if ($returnType === ': void') {
                $methods .= "
                public function {$method->getName()}(" . implode(', ', $params) . "): void
                {
                    \$this->proxy->call('{$method->getName()}', [" . implode(', ', $args) . "]);
                }";
            } else {
                $methods .= "
                public function {$method->getName()}(" . implode(', ', $params) . ")$returnType
                {
                    return \$this->proxy->call('{$method->getName()}', [" . implode(', ', $args) . "]);
                }";
            }
        }

        $class = $reflection->getShortName() . 'Proxy';

        $code = "
            class $class implements \\$interface
            {
                public function __construct(
                    private readonly \\Core\\ServiceProxy\\ServiceProxy \$proxy
                ) {}

                $methods
            }
        ";

        eval($code);

        return $class;
    }
}
