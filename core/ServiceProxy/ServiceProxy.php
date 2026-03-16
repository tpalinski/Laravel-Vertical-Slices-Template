<?php

declare(strict_types=1);

namespace Core\ServiceProxy;

use Core\Annotations\Feature;
use Core\Exception\Feature\FeatureNotEnabledException;
use ReflectionMethod;

class ServiceProxy {

    public function __construct(
        private array $featureFlags,
        private object $service,
    ) {}

    private function featureEnabled(string $flag): bool {
        return $this->featureFlags[$flag] ?? false;
    }

    public function call($method, $arguments) {
        $reflection = new ReflectionMethod($this->service, $method);
        $attributes = [];
        $attributes = array_merge(
            $attributes,
            $reflection->getAttributes(Feature::class)
        );
        $class = $reflection->getDeclaringClass();
        foreach ($class->getInterfaces() as $interface) {
            if ($interface->hasMethod($method)) {
                $attributes = array_merge(
                    $attributes,
                    $interface->getMethod($method)->getAttributes(Feature::class)
                );
            }
        }
        foreach ($attributes as $attribute) {
            $feature = $attribute->newInstance()->featureName;
            if (!$this->featureEnabled($feature)) {
                throw new FeatureNotEnabledException(
                    "Feature {$feature} is not enabled"
                );
            }
        }
        return $this->service->$method(...$arguments);
    }
}
