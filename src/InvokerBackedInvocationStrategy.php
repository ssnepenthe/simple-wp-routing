<?php

declare(strict_types=1);

namespace ToyWpRouting;

use Invoker\InvokerInterface;

class InvokerBackedInvocationStrategy implements InvocationStrategyInterface
{
    protected $context = [];
    protected $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function invokeHandler(RewriteInterface $rewrite)
    {
        return $this->invoker->call(
            $rewrite->getHandler(),
            $this->resolveAdditionalParameters($rewrite)
        );
    }

    public function invokeIsActiveCallback(RewriteInterface $rewrite)
    {
        $callback = $rewrite->getIsActiveCallback();

        if (null === $callback) {
            return true;
        }

        return (bool) $this->invoker->call($callback);
    }

    public function withAdditionalContext(array $context)
    {
        // @todo Clone this instance with new context and return that instead?
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    protected function resolveAdditionalParameters(RewriteInterface $rewrite)
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
