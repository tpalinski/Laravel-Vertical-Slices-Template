<?php

declare(strict_types=1);

namespace Core\ServiceProxy;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class CoreProxyGenerator
{
    private string $basePath;
    private string $baseNamespace = 'Core\\Proxies';

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? dirname(__DIR__, 2) . '/core/Proxies';
    }

    public function generateProxy(string $interface, object $service): object
    {
        $reflection = new ReflectionClass($interface);

        $shortName = $reflection->getShortName();
        $hash = md5($interface . filemtime($reflection->getFileName()));

        $className = $shortName . 'Proxy_' . substr($hash, 0, 16);
        $fqcn = $this->baseNamespace . '\\' . $className;

        $file = $this->basePath . '/' . $className . '.php';

        if (!class_exists($fqcn, false)) {

            if (!file_exists($file)) {
                $this->generateFile($reflection, $className, $file);
            }

            class_exists($file);
        }

        return new $fqcn($service);
    }

    private function generateFile(ReflectionClass $reflection, string $className, string $file): void
    {
        $methods = '';

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods .= $this->generateMethod($method);
        }

        $interface = '\\' . ltrim($reflection->getName(), '\\');

        $code = "<?php

declare(strict_types=1);

namespace {$this->baseNamespace};

class $className implements $interface
{
    public function __construct(
        private readonly \\Core\\ServiceProxy\\ServiceProxy \$proxy
    ) {}

$methods
}
";

        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }

        file_put_contents($file, $code);

        if (function_exists('opcache_compile_file')) {
            @opcache_compile_file($file);
        }
    }

    private function generateMethod(ReflectionMethod $method): string
    {
        $params = [];
        $args = [];

        foreach ($method->getParameters() as $param) {
            $paramCode = '';

            if ($param->hasType()) {
                $type = $param->getType();
                $nullable = $type->allowsNull() && !$type instanceof ReflectionUnionType ? '?' : '';
                $paramCode .= $nullable . $this->normalizeType($type) . ' ';
            }

            if ($param->isPassedByReference()) {
                $paramCode .= '&';
            }

            if ($param->isVariadic()) {
                $paramCode .= '...';
            }

            $paramCode .= '$' . $param->getName();

            if ($param->isDefaultValueAvailable() && !$param->isVariadic()) {
                $paramCode .= ' = ' . var_export($param->getDefaultValue(), true);
            }

            $params[] = $paramCode;
            $args[] = ($param->isVariadic() ? '...' : '') . '$' . $param->getName();
        }

        $returnType = '';

        if ($method->hasReturnType()) {
            $type = $method->getReturnType();
            $nullable = $type->allowsNull() && !$type instanceof ReflectionUnionType ? '?' : '';
            $returnType = ': ' . $nullable . $this->normalizeType($type);
        }

        $methodName = $method->getName();

        if ($returnType === ': void') {
            return "
    public function $methodName(" . implode(', ', $params) . "): void
    {
        \$this->proxy->call('$methodName', [" . implode(', ', $args) . "]);
    }
";
        }

        return "
    public function $methodName(" . implode(', ', $params) . ")$returnType
    {
        return \$this->proxy->call('$methodName', [" . implode(', ', $args) . "]);
    }
";
    }

    private function normalizeType(ReflectionType $type): string
    {
        if ($type instanceof ReflectionUnionType) {
            $types = [];

            foreach ($type->getTypes() as $t) {
                $types[] = $this->normalizeSingleType($t);
            }

            return implode('|', $types);
        }

        return $this->normalizeSingleType($type);
    }

    private function normalizeSingleType(ReflectionNamedType $type): string
    {
        $name = $type->getName();

        if (in_array($name, ['self', 'static', 'parent'], true)) {
            return $name;
        }

        if ($type->isBuiltin()) {
            return $name;
        }

        return '\\' . ltrim($name, '\\');
    }
}
