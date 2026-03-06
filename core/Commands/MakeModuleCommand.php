<?php

declare(strict_types=1);

namespace Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Artisan command: php artisan make:module {name}
 *
 * Scaffolds a complete module under src/Modules/{Name}
 *
 * And generates:
 *   - {Name}ServiceProvider.php
 *   - A starter config file
 *
 * After running, add the provider to CoreServiceProvider::$modules.
 */
class MakeModuleCommand extends Command
{
    protected $signature   = 'make:module {name : The PascalCase module name (e.g. Blog)}';
    protected $description = 'Scaffold a new Modular Monolith module';

    public function handle(): int
    {
        $name   = Str::studly($this->argument('name'));
        $lower  = Str::lower($name);
        $base   = base_path("modules/{$name}");

        $directories = [
            'Rest/Controller', 'Rest/routes', 'Rest/Middleware','Architecture/Service', 'Architecture/Job', 'Architecture/Providers', 'Domain/Service', 'Domain/DTO', 'Domain/Exception', 'Persistence/migrations', 'Persistence/Model', 'Persistence/Factory', 'Persistence/Seeder', 'config',
            'Test/Unit', 'Test/Feature',
        ];

        foreach ($directories as $dir) {
            $path = "{$base}/{$dir}";
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
                $this->line("  <comment>created</comment>  {$path}");
            }
        }

        $this->generateResourceProvider($base, $name, $lower);
        $this->generateServiceProvider($base, $name, $lower);
        $this->generateConfig($base, $lower);
        $this->generateApiRoutes($base, $name, $lower);
        $this->generateManifest($base, $name);

        $this->newLine();
        $this->info("Module [{$name}] scaffolded successfully.");

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------

    private function generateResourceProvider(string $base, string $name, string $lower): void
    {
        $path = "{$base}/{$name}ResourceProvider.php";

        if (file_exists($path)) {
            return;
        }

        $stub = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$name};

use Core\Providers\ModuleResourceProvider;

class {$name}ResourceProvider extends ModuleResourceProvider
{
    protected function modulePath(): string {
        return base_path('modules/{$name}');
    }

    protected function routePrefix(): string {
        return '{$lower}';
    }
}
PHP;
        file_put_contents($path, $stub);
        $this->line("  <info>generated</info> {$path}");
    }

    private function generateServiceProvider(string $base, string $name, string $lower): void
    {
        $path = "{$base}/Architecture/Providers/{$name}ServiceProvider.php";

        if (file_exists($path)) {
            return;
        }

        $stub = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$name}\\Architecture\Providers;

use Core\Providers\ModuleServiceProvider;

class {$name}ResourceProvider extends ModuleServiceProvider
{
    public function provides(): array {
        return [];
    }

    protected function registerBindings(): void {

    }
}
PHP;
        file_put_contents($path, $stub);
        $this->line("  <info>generated</info> {$path}");
    }

    private function generateConfig(string $base, string $lower): void
    {
        $path = "{$base}/config/{$lower}.php";

        if (file_exists($path)) {
            return;
        }

        $stub = <<<PHP
<?php

return [
    // {$lower} module configuration
];
PHP;
        file_put_contents($path, $stub);
        $this->line("  <info>generated</info> {$path}");
    }

    private function generateApiRoutes(string $base, string $name, string $lower): void
    {
        $path = "{$base}/Rest/routes/api.php";

        if (file_exists($path)) {
            return;
        }

        $stub = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/{$lower}')->name('{$lower}.')->group(function () {
    // Define {$name} module routes here
});
PHP;
        file_put_contents($path, $stub);
        $this->line("  <info>generated</info> {$path}");
    }

    private function generateManifest(string $base, string $name) {
        $manifestPath = rtrim($base, '/').'/module.json';

        if (file_exists($manifestPath)) {
            return;
        }

        $manifest = [
            "name" => $name,
            "version" => "1.0.0",
            "enabled" => true,
            "environment" => "local",
            "dependencies" => [],
            "features" => [],
            "resourceProvider" => "Modules\\{$name}\\{$name}ResourceProvider",
            "serviceProvider" => "Modules\\{$name}\\Architecture\Providers\\{$name}ServiceProvider",
        ];

        file_put_contents(
            $manifestPath,
            json_encode(
                $manifest,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        $this->info("Manifest generated: {$manifestPath}");
    }
}
