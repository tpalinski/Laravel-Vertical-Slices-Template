<?php

declare(strict_types=1);

namespace Modules\Pibble\Domain\DTO;

use Spatie\LaravelData\Data;

class PibbleResponseDto extends Data {
    public string $name;
    public bool $bellyWashed;
}
