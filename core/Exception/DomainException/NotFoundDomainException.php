<?php

declare(strict_types=1);

namespace Core\Exception\DomainException;

use Core\Exception\HttpExceptionCode;

abstract class NotFoundDomainException extends DomainException {
    public HttpExceptionCode $code = HttpExceptionCode::NOT_FOUND;
}
