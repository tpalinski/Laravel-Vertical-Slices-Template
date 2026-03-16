<?php

declare(strict_types=1);

namespace Core\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CoreCache extends FilesystemAdapter {
    public function __construct() {
        return parent::__construct('core', 3600, __DIR__.'/../../bootstrap/cache', null);
    }
}
