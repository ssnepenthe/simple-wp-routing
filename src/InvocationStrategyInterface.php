<?php

declare(strict_types=1);

namespace ToyWpRouting;

interface InvocationStrategyInterface
{
    public function invoke($callable, array $context = []);
}
