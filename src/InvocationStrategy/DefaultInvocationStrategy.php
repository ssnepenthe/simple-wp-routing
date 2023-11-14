<?php

declare(strict_types=1);

namespace ToyWpRouting\InvocationStrategy;

class DefaultInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @param mixed $callable
     *
     * @return mixed
     */
    public function invoke($callable, array $context = [])
    {
        return $callable($context);
    }
}
