<?php

declare(strict_types=1);

namespace Modules\Pibble\Architecture\Service;

use Modules\Pibble\Domain\Service\PibbleServiceInterface;
use Modules\Pibble\Persistence\Model\Pibble;

class PibbleService implements PibbleServiceInterface {
    public function greetPibble(): string
    {
        return "Hi I am pibble. Wash my belly.";
    }

    public function getPibble(string $name): Pibble
    {
        $res = Pibble::where('name', $name)->first();
        return $res;
    }

    public function createPibble(string $name): void
    {
        throw new \Exception('Not implemented');
    }

    public function washBelly(string $name): bool
    {
        throw new \Exception('Not implemented');
    }
}
