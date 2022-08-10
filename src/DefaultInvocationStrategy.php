<?php

declare(strict_types=1);

namespace ToyWpRouting;

class DefaultInvocationStrategy extends AbstractInvocationStrategy
{
    public function invoke($callable, array $context = [])
    {
        return ($this->resolveCallable($callable))($context);
    }
}
