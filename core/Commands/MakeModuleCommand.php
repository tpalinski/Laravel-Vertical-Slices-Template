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

        $this->generateServiceProvider($base, $name, $lower);
        $this->generateConfig($base, $lower);
        $this->generateApiRoutes($base, $name, $lower);

        $this->newLine();
        $this->info("Module [{$name}] scaffolded successfully.");
        $this->line("  Next: add <fg=yellow>Modules\\{$name}\\{$name}ModuleProvider::class</> to <fg=yellow>CoreServiceProvider::\$modules</>.");

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------

    private function generateServiceProvider(string $base, string $name, string $lower): void
    {
        $path = "{$base}/{$name}ModuleProvider.php";

        if (file_exists($path)) {
            return;
        }

        $stub = <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$name};

use Core\Providers\ModuleServiceProvider;

class {$name}ModuleProvider extends ModuleServiceProvider
{
    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }

    protected function routePrefix(): string
    {
        return '{$lower}';
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
}
