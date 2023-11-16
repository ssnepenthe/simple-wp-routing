<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder;

use SimpleWpRouting\Exception\HttpExceptionInterface;

final class HttpExceptionResponder extends Responder
{
    private HttpExceptionInterface $exception;

    public function __construct(HttpExceptionInterface $exception)
    {
        $this->exception = $exception;
    }

    public function respond(): void
    {
        $this->exception->prepareResponse($this);

        parent::respond();
    }
}
