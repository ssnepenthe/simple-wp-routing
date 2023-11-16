<?php

declare(strict_types=1);

namespace SimpleWpRouting\InvocationStrategy;

use Invoker\Invoker;
use Invoker\InvokerInterface;

final class InvokerBackedInvocationStrategy implements InvocationStrategyInterface
{
    private InvokerInterface $invoker;

    public function __construct(?InvokerInterface $invoker = null)
    {
        $this->invoker = $invoker ?: new Invoker();
    }

    /**
     * @return mixed
     */
    public function invoke(callable $callable, array $context = [])
    {
        return $this->invoker->call($callable, $context);
    }
}
