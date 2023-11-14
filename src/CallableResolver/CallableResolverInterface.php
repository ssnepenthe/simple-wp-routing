<?php

declare(strict_types=1);

namespace ToyWpRouting\CallableResolver;

use ToyWpRouting\Exception\BadCallableException;

interface CallableResolverInterface
{
    /**
     * @param mixed $value
     * @throws BadCallableException
     */
    public function resolve($value): callable;
}
