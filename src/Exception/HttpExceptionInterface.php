<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use Throwable;
use ToyWpRouting\Responder\HttpExceptionResponder;

interface HttpExceptionInterface extends Throwable
{
    public function prepareResponse(HttpExceptionResponder $responder): void;
}
