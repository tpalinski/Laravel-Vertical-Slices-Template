<?php

declare(strict_types=1);

namespace Modules\Pibble\Architecture\Service;

use Illuminate\Database\QueryException;
use Modules\Pibble\Architecture\Repository\PibbleRepository;
use Modules\Pibble\Domain\Exception\PibbleAlreadyExistsException;
use Modules\Pibble\Domain\Exception\PibbleNotFoundException;
use Modules\Pibble\Domain\Model\Pibble;
use Modules\Pibble\Domain\Service\PibbleServiceInterface;

class PibbleService implements PibbleServiceInterface {

    public function __construct(
        private readonly PibbleRepository $repository,
    ) {}

    public function greetPibble(): string {
        return config('pibble.message', "Could not find message in config");
    }

    public function getPibble(string $name): Pibble
    {
        $pibble = $this->repository->getByField('name', $name);
        if (!$pibble) {
            throw new PibbleNotFoundException("No such Pibble exists");
        }
        return $pibble;
    }

    public function createPibble(string $name): Pibble
    {
        $pibble = new Pibble();
        $pibble->name = $name;
        try {
            return $this->repository->create($pibble);
        } catch (QueryException $e) {
            throw new PibbleAlreadyExistsException("Pibble with such name already exists");
        }
    }

    public function washBelly(string $name): bool
    {
        $pibble = $this->repository->getByField("name", $name);
        if (!$pibble) {
            throw new PibbleNotFoundException("No such Pibble exists");
        }
        if ($pibble->belly_washed) {
            return false;
        } else {
            $pibble->belly_washed = true;
            $this->repository->update($pibble);
            return true;
        }
    }
}
