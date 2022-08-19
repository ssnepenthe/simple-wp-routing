<?php

declare(strict_types=1);

namespace ToyWpRouting;

abstract class AbstractInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @var callable|null
     */
    protected $callableResolver;

    public function getCallableResolver(): ?callable
    {
        return $this->callableResolver;
    }

    abstract public function invoke($callable, array $context = []);

    public function setCallableResolver(callable $callableResolver): void
    {
        $this->callableResolver = $callableResolver;
    }

    protected function resolveCallable($potentialCallable)
    {
        if (is_callable($this->callableResolver)) {
            return ($this->callableResolver)($potentialCallable);
        }

        return $potentialCallable;
    }
}
