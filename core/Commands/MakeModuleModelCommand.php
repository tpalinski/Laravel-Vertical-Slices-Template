<?php

namespace Core\Commands;

use Illuminate\Console\Command;

class MakeModuleModelCommand extends Command
{
    protected $signature = 'module:make-model
                            {module : Module name}
                            {name : Model name}
                            {--migration';

    protected $description = 'Create a model inside a module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $name   = $this->argument('name');

        $modelPath = base_path("modules/{$module}/Persistence/Model");

        if (! is_dir($modelPath)) {
            mkdir($modelPath, 0755, true);
        }

        $className = ucfirst($name);

        $filePath = "{$modelPath}/{$className}.php";

        if (file_exists($filePath)) {
            $this->error("Model already exists.");
            return Command::FAILURE;
        }

        $namespace = "Modules\\{$module}\\Persistence\Model";

        $content = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class {$className} extends Model
{
    use HasFactory;

    protected \$table = strtolower('{$name}s');

    protected \$fillable = [];
}
PHP;

        file_put_contents($filePath, $content);

        $this->info("Model created: {$filePath}");
        $this->createFactory($module, $name);

        return Command::SUCCESS;
    }

    protected function createFactory(string $module, string $name): void {
        $factoryPath = base_path("modules/{$module}/Persistence/Factory");

        if (! is_dir($factoryPath)) {
            mkdir($factoryPath, 0755, true);
        }

        $className = $name . 'Factory';

        $filePath = "{$factoryPath}/{$className}.php";

        if (file_exists($filePath)) {
            $this->error("Factory already exists.");
            return;
        }

        $modelNamespace = "Modules\\{$module}\\Persistence\Model\\{$name}";
        $factoryNamespace = "Modules\\{$module}\\Persistence\\Factory";

        $content = <<<PHP
<?php

namespace {$factoryNamespace};

use Illuminate\Database\Eloquent\Factories\Factory;

class {$className} extends Factory
{
    protected \$model = \\{$modelNamespace}::class;

    public function definition(): array
    {
        return [
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
PHP;

        file_put_contents($filePath, $content);

        $this->info("Factory created: {$filePath}");
    }
}
