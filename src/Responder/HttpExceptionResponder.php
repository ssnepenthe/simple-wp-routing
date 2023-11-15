<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder;

use SimpleWpRouting\Exception\HttpExceptionInterface;

final class HttpExceptionResponder extends Responder
{
    public function __construct(HttpExceptionInterface $exception)
    {
        $exception->prepareResponse($this);
    }
}
