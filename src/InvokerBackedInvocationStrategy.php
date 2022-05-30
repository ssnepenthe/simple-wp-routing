<?php

declare(strict_types=1);

namespace ToyWpRouting;

use Invoker\InvokerInterface;

class InvokerBackedInvocationStrategy extends AbstractInvocationStrategy
{
    protected $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function invokeHandler(RewriteInterface $rewrite)
    {
        // @todo Should this also receive full additional context as second param?
        return $this->invoker->call(
            $this->resolveCallable($rewrite->getHandler()),
            $this->resolveRelevantQueryVariablesFromContext($rewrite)
        );
    }

    public function invokeIsActiveCallback(RewriteInterface $rewrite)
    {
        $callback = $rewrite->getIsActiveCallback();

        if (null === $callback) {
            return true;
        }

        // @todo Should this get additional context?
        return (bool) $this->invoker->call($this->resolveCallable($callback));
    }
}
