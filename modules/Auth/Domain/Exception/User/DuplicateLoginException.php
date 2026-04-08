<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Exception\User;

use Core\Exception\DomainException;

class DuplicateLoginException extends DomainException {}
