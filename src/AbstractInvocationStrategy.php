<?php

declare(strict_types=1);

namespace ToyWpRouting;

abstract class AbstractInvocationStrategy implements InvocationStrategyInterface
{
    protected $context = [];

    public function getContext(): array
    {
        return $this->context;
    }

    abstract public function invokeHandler(RewriteInterface $rewrite);

    abstract public function invokeIsActiveCallback(RewriteInterface $rewrite);

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

    protected function resolveRelevantQueryVariablesFromContext(RewriteInterface $rewrite)
    {
        if (! array_key_exists('queryVars', $this->context)) {
            return [];
        }

        $resolved = [];

        // @todo also in snake case?
        // @todo Test with optional params.
        // @todo Include all query vars?
        // @todo Include prefixed query vars as well?
        foreach ($rewrite->getPrefixedToUnprefixedQueryVariablesMap() as $prefixed => $unprefixed) {
            if (array_key_exists($prefixed, $this->context['queryVars'])) {
                $resolved[$unprefixed] = $this->context['queryVars'][$prefixed];
            }
        }

        return $resolved;
    }
}
