<?php

declare(strict_types=1);

namespace SimpleWpRouting\Exception;

use Throwable;
use SimpleWpRouting\Responder\HttpExceptionResponder;

interface HttpExceptionInterface extends Throwable
{
    public function prepareResponse(HttpExceptionResponder $responder): void;
}
