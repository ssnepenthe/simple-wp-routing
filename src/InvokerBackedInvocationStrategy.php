<?php

declare(strict_types=1);

namespace ToyWpRouting;

use Invoker\Invoker;
use Invoker\InvokerInterface;

class InvokerBackedInvocationStrategy extends AbstractInvocationStrategy
{
    protected InvokerInterface $invoker;

    public function __construct(?InvokerInterface $invoker = null)
    {
        $this->invoker = $invoker ?: new Invoker();
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
