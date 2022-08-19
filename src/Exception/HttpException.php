<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use RuntimeException;
use ToyWpRouting\Responder\HttpExceptionResponder;

// @todo Shouldn't be abstract?
abstract class HttpException extends RuntimeException implements HttpExceptionInterface
{
    abstract public function prepareResponse(HttpExceptionResponder $responder): void;
}
