<?php

declare(strict_types=1);

namespace Modules\Pibble\Domain\Service;

use Modules\Pibble\Domain\Exception\PibbleAlreadyExistsException;
use Modules\Pibble\Domain\Exception\PibbleNotFoundException;
use Modules\Pibble\Domain\Model\Pibble;

interface PibbleServiceInterface {
    public function greetPibble(): string;
    /**
     * @throws PibbleNotFoundException
     */
    public function getPibble(string $name): Pibble;
    /**
     * @throws PibbleAlreadyExistsException
     */
    public function createPibble(string $name): Pibble;
    /**
     * @throws PibbleNotFoundException
     */
    public function washBelly(string $name): bool;
}
