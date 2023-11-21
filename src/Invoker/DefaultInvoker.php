<?php

declare(strict_types=1);

namespace SimpleWpRouting\Invoker;

class DefaultInvoker implements InvokerInterface
{
    /**
     * @return mixed
     */
    public function invoke(callable $callable, array $context = [])
    {
        return $callable($context);
    }
}
