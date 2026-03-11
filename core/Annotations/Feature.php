<?php

declare(strict_types=1);

namespace Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Feature {
    public function __construct(
        public string $featureName,
    ) {}
}
