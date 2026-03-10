<?php

declare(strict_types=1);

namespace Modules\Pibble\Domain\DTO;

use Spatie\LaravelData\Data;

class PibbleRequestDto extends Data {
    public readonly string $name;
}
