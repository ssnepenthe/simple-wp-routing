<?php

namespace ToyWpRouting;

use ToyWpRouting\Exception\BadCallableException;

interface CallableResolverInterface
{
    /**
     * @param mixed $value
     * @throws BadCallableException
     */
    public function resolve($value): callable;
}
