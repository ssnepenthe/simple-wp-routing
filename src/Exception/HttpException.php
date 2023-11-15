<?php

declare(strict_types=1);

namespace SimpleWpRouting\Exception;

use RuntimeException;
use Throwable;
use SimpleWpRouting\Responder\HttpExceptionResponder;
use SimpleWpRouting\Responder\Partial\HeadersPartial;

class HttpException extends RuntimeException implements HttpExceptionInterface
{
    protected array $headers;

    protected int $statusCode;

    public function __construct(
        int $statusCode,
        string $message = '',
        ?Throwable $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function prepareResponse(HttpExceptionResponder $responder): void
    {
        $responder->getPartialSet()
            ->get(HeadersPartial::class)
            ->setStatusCode($this->statusCode)
            ->setHeaders($this->headers);

        $this->doPrepareResponse($responder);
    }

    protected function doPrepareResponse(HttpExceptionResponder $responder): void
    {
        // Nothing by default...
    }
}
