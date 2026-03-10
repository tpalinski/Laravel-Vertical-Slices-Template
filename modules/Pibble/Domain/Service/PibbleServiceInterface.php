<?php

declare(strict_types=1);

namespace Modules\Pibble\Domain\Service;

use Modules\Pibble\Domain\Model\Pibble;

interface PibbleServiceInterface {
    public function greetPibble(): string;
    public function getPibble(string $name): Pibble;
    public function createPibble(string $name): void;
    public function washBelly(string $name): bool;
}
