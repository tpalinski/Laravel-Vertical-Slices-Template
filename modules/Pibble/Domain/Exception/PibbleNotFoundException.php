<?php

declare(strict_types=1);

namespace Modules\Pibble\Domain\Exception;

use Core\Exception\DomainException\NotFoundDomainException;

class PibbleNotFoundException extends NotFoundDomainException {}
