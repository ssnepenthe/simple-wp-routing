<?php

declare(strict_types=1);

namespace SimpleWpRouting\CallableResolver;

use SimpleWpRouting\Exception\BadCallableException;

interface CallableResolverInterface
{
    /**
     * @param mixed $value
     *
     * @throws BadCallableException
     */
    public function resolve($value): callable;
}
