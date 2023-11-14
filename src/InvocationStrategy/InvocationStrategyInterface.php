<?php

declare(strict_types=1);

namespace ToyWpRouting\InvocationStrategy;

interface InvocationStrategyInterface
{
    /**
     * @param mixed $callable
     *
     * @return mixed
     */
    public function invoke($callable, array $context = []);
}
