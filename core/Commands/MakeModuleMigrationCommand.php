<?php

declare(strict_types=1);

namespace Core\Commands;

use Illuminate\Console\Command;

class MakeModuleMigrationCommand extends Command
{
    protected $signature = 'module:make-migration
                            {module : Module name}
                            {name : Migration name}
                            {--create= : Table to create}
                            {--table= : Table name}';

    protected $description = 'Create a migration inside a module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $name   = $this->argument('name');

        $path = base_path("modules/{$module}/Persistence/migrations");

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $this->info("Creating migration for module {$module}");

        $this->call('make:migration', array_filter([
            'name' => $name,
            '--path' => "modules/{$module}/Persistence/migrations",
            '--create' => $this->option('create'),
            '--table' => $this->option('table'),
        ]));

        return Command::SUCCESS;
    }
}
