<?php

declare(strict_types=1);

namespace Core\Exception\HttpException;

use Core\Enum\HttpCode;
use Throwable;

class HttpBadRequestException extends HttpException {

    public function __construct(string $message = '', array $headers = [], ?Throwable $previous = null)
    {
        $statusCode = HttpCode::BAD_REQUEST;
        return parent::__construct($statusCode, $message, $headers, $previous);
    }
}
