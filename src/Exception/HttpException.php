<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use RuntimeException;
use Throwable;
use ToyWpRouting\Responder\HttpExceptionResponder;

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

    public function prepareResponse(HttpExceptionResponder $responder): void
    {
        $responder
            ->withStatusCode($this->statusCode)
            ->withHeaders($this->headers);

        if (method_exists($this, 'doPrepareResponse')) {
            $this->doPrepareResponse($responder);
        }
    }
}
