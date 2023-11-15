<?php

declare(strict_types=1);

namespace SimpleWpRouting\InvocationStrategy;

interface InvocationStrategyInterface
{
    /**
     * @param mixed $callable
     *
     * @return mixed
     */
    public function invoke($callable, array $context = []);
}
