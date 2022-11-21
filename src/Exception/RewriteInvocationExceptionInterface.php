<?php

namespace ToyWpRouting\Exception;

use Throwable;

interface RewriteInvocationExceptionInterface extends Throwable
{
    public function toHttpException(): HttpExceptionInterface;
}
