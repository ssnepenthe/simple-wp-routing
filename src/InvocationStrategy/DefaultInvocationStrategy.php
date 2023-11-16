<?php

declare(strict_types=1);

namespace SimpleWpRouting\InvocationStrategy;

class DefaultInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @return mixed
     */
    public function invoke(callable $callable, array $context = [])
    {
        return $callable($context);
    }
}
