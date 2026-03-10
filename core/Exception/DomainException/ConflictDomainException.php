
<?php

declare(strict_types=1);

namespace Core\Exception\DomainException;

use Core\Exception\HttpExceptionCode;

abstract class ConflictDomainException extends DomainException {
    public HttpExceptionCode $code = HttpExceptionCode::CONFLICT;
}
