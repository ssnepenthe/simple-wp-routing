<?php

declare(strict_types=1);

namespace ToyWpRouting\InvocationStrategy;

use Invoker\Invoker;
use Invoker\InvokerInterface;

class InvokerBackedInvocationStrategy implements InvocationStrategyInterface
{
    protected InvokerInterface $invoker;

    public function __construct(?InvokerInterface $invoker = null)
    {
        $this->invoker = $invoker ?: new Invoker();
    }

    /**
     * @param mixed $callable
     *
     * @return mixed
     */
    public function invoke($callable, array $context = [])
    {
        return $this->invoker->call($callable, $context);
    }
}
