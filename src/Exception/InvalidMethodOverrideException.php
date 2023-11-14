<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use UnexpectedValueException;

final class InvalidMethodOverrideException extends UnexpectedValueException implements RewriteInvocationExceptionInterface
{
    public function toHttpException(): HttpExceptionInterface
    {
        return new BadRequestHttpException('', $this);
    }
}
