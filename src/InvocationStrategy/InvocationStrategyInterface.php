<?php

declare(strict_types=1);

namespace SimpleWpRouting\InvocationStrategy;

interface InvocationStrategyInterface
{
    /**
     * @return mixed
     */
    public function invoke(callable $callable, array $context = []);
}
