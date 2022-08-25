<?php

declare(strict_types=1);

namespace ToyWpRouting;

abstract class AbstractInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @var callable|null
     */
    protected $callableResolver;

    /**
     * @template T
     *
     * @psalm-return ?callable(T):T|callable
     */
    public function getCallableResolver(): ?callable
    {
        return $this->callableResolver;
    }

    abstract public function invoke($callable, array $context = []);

    /**
     * @template T
     *
     * @psalm-param callable(T):T|callable $callableResolver
     */
    public function setCallableResolver(callable $callableResolver): void
    {
        $this->callableResolver = $callableResolver;
    }

    /**
     * @template T
     *
     * @psalm-param T $potentialCallable
     *
     * @psalm-return T|callable
     */
    protected function resolveCallable($potentialCallable)
    {
        if (is_callable($this->callableResolver)) {
            return ($this->callableResolver)($potentialCallable);
        }

        return $potentialCallable;
    }
}
