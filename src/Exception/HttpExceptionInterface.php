<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use ToyWpRouting\Responder\HttpExceptionResponder;

interface HttpExceptionInterface
{
    public function prepareResponse(HttpExceptionResponder $responder): void;
}
