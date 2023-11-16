<?php

declare(strict_types=1);

namespace SimpleWpRouting\Invoker;

interface InvokerInterface
{
    /**
     * @return mixed
     */
    public function invoke(callable $callable, array $context = []);
}
