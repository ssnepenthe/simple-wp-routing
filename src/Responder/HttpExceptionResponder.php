<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Exception\HttpExceptionInterface;

class HttpExceptionResponder extends Responder
{
    public function __construct(HttpExceptionInterface $exception)
    {
        $exception->prepareResponse($this);
    }
}
