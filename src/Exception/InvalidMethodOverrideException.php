<?php

declare(strict_types=1);

namespace SimpleWpRouting\Exception;

use UnexpectedValueException;

final class InvalidMethodOverrideException extends UnexpectedValueException implements RewriteInvocationExceptionInterface
{
    public function toHttpException(): HttpExceptionInterface
    {
        return new BadRequestHttpException('', $this);
    }
}
