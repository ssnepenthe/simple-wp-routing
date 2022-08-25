<?php

declare(strict_types=1);

namespace ToyWpRouting;

class DefaultInvocationStrategy extends AbstractInvocationStrategy
{
    /**
     * @param mixed $callable
     *
     * @return mixed
     */
    public function invoke($callable, array $context = [])
    {
        return ($this->resolveCallable($callable))($context);
    }
}
