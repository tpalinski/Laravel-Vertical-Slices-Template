<?php

declare(strict_types=1);

namespace Core\Commands;

use Core\Cache\CoreCache;
use Illuminate\Console\Command;

class ClearInternalCacheCommand extends Command {
    protected $signature = 'core:clear-cache';
    protected $description = 'Clear Laravel optimize cache and CoreCache';

    public function handle(): int
    {
        $this->call('optimize:clear');

        $cache = new CoreCache();
        $cache->clear();

        $this->info('Optimize cache and CoreCache cleared successfully.');

        return self::SUCCESS;
    }
}
