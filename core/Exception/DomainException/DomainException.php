<?php

declare(strict_types=1);

namespace Core\Exception\DomainException;

use Core\Exception\HttpExceptionCode;
use Exception;

abstract class DomainException extends Exception {
    public HttpExceptionCode $code;
}
