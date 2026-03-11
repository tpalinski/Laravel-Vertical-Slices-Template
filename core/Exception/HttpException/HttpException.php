<?php

declare(strict_types=1);

namespace Core\Exception\HttpException;

use Core\Enum\HttpCode;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class HttpException extends RuntimeException implements HttpExceptionInterface
{
    protected int $statusCode;
    protected array $headers;

    public function __construct(
        HttpCode $statusCode,
        string $message = '',
        array $headers = [],
        ?Throwable $previous = null
    ) {
        $this->statusCode = $statusCode->code();
        $this->headers = $headers;

        parent::__construct($message, $this->statusCode, $previous);
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
