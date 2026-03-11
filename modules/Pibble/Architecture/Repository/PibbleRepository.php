<?php

declare(strict_types=1);

namespace Modules\Pibble\Architecture\Repository;

use Core\Repository\Repository;
use Modules\Pibble\Domain\Model\Pibble;

class PibbleRepository extends Repository {

    public function model(): string
    {
        return Pibble::class;
    }
}
