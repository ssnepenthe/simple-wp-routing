<?php

declare(strict_types=1);

namespace ToyWpRouting\Exception;

use RuntimeException;

class RewriteDisabledException extends RuntimeException implements RewriteInvocationExceptionInterface
{
    public function toHttpException(): HttpExceptionInterface
    {
        return new NotFoundHttpException('', $this);
    }
}
