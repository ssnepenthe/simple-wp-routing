<?php

namespace ToyWpRouting\CallableResolver;

use ToyWpRouting\Exception\BadCallableException;

class DefaultCallableResolver implements CallableResolverInterface
{
    public function resolve($value): callable
    {
        if (! is_callable($value)) {
            throw new BadCallableException('Value ' . var_export($value, true) . ' is not callable');
        }

        return $value;
    }
}
