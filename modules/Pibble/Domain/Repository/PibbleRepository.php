<?php

declare(strict_types=1);

namespace Modules\Pibble\Domain\Repository;

use Core\Repository\Repository;
use Modules\Pibble\Persistence\Model\Pibble;

class PibbleRepository extends Repository {

    public function model(): string
    {
        return Pibble::class;
    }
}
