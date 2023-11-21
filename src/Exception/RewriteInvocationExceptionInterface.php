<?php

declare(strict_types=1);

namespace SimpleWpRouting\Exception;

use Throwable;

interface RewriteInvocationExceptionInterface extends Throwable
{
    public function toHttpException(): HttpExceptionInterface;
}
