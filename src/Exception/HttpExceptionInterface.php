<?php

declare(strict_types=1);

namespace SimpleWpRouting\Exception;

use SimpleWpRouting\Responder\HttpExceptionResponder;
use Throwable;

interface HttpExceptionInterface extends Throwable
{
    public function prepareResponse(HttpExceptionResponder $responder): void;
}
