<?php

declare(strict_types=1);

namespace Core\Exception;

use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class HttpException extends RuntimeException implements HttpExceptionInterface
{
    protected int $statusCode;
    protected array $headers;

    public function __construct(
        HttpExceptionCode $statusCode,
        string $message = '',
        array $headers = [],
        ?Throwable $previous = null
    ) {
        $this->statusCode = $statusCode->value;
        $this->headers = $headers;

        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
