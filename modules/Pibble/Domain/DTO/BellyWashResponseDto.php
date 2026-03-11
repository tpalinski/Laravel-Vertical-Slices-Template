<?php

declare(strict_types=1);

namespace Modules\Pibble\Domain\DTO;

use Spatie\LaravelData\Data;

class BellyWashResponseDto extends Data {
    public bool $cleanedRealGood;
}
