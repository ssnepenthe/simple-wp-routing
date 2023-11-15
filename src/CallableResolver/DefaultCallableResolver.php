<?php

declare(strict_types=1);

namespace ToyWpRouting\CallableResolver;

use ToyWpRouting\Exception\BadCallableException;

final class DefaultCallableResolver implements CallableResolverInterface
{
    /**
     * @param mixed $value
     *
     * @throws BadCallableException
     */
    public function resolve($value): callable
    {
        if (! is_callable($value)) {
            throw new BadCallableException('Value ' . var_export($value, true) . ' is not callable');
        }

        return $value;
    }
}
