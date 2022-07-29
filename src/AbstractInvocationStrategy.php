<?php

declare(strict_types=1);

namespace ToyWpRouting;

abstract class AbstractInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @var callable|null
     */
    protected $callableResolver;

    protected array $context = [];

    public function getCallableResolver(): ?callable
    {
        return $this->callableResolver;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    abstract public function invokeHandler(RewriteInterface $rewrite);

    abstract public function invokeIsActiveCallback(RewriteInterface $rewrite);

    public function setCallableResolver(callable $callableResolver): void
    {
        $this->callableResolver = $callableResolver;
    }

    public function withAdditionalContext(array $context): InvocationStrategyInterface
    {
        $strategy = clone $this;
        $strategy->context = array_merge($this->context, $context);

        return $strategy;
    }

    public function withContext(array $context): InvocationStrategyInterface
    {
        $strategy = clone $this;
        $strategy->context = $context;

        return $strategy;
    }

    protected function resolveCallable($potentialCallable)
    {
        if (is_callable($this->callableResolver)) {
            return ($this->callableResolver)($potentialCallable);
        }

        return $potentialCallable;
    }

    protected function resolveRelevantQueryVariablesFromContext(RewriteInterface $rewrite): array
    {
        if (! array_key_exists('queryVars', $this->context)) {
            return [];
        }

        $resolved = [];

        // @todo also in snake case?
        // @todo Test with optional params.
        // @todo Include all query vars?
        // @todo Include prefixed query vars as well?
        foreach ($this->context['queryVars'] as $key => $value) {
            if (is_string($newKey = $rewrite->mapQueryVariable($key))) {
                $resolved[$newKey] = $value;
            }
        }

        return $resolved;
    }
}
