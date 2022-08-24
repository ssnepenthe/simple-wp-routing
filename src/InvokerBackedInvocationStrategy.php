<?php

declare(strict_types=1);

namespace ToyWpRouting;

use Invoker\Invoker;
use Invoker\InvokerInterface;

class InvokerBackedInvocationStrategy extends AbstractInvocationStrategy
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
        return $this->invoker->call($this->resolveCallable($callable), $context);
    }
}
