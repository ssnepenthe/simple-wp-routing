<?php

declare(strict_types=1);

namespace ToyWpRouting;

class DefaultInvocationStrategy extends AbstractInvocationStrategy
{
    public function invokeHandler(RewriteInterface $rewrite)
    {
        // @todo Should this receive full additional context as second param?
        return ($this->resolveCallable($rewrite->getHandler()))(
            $this->resolveRelevantQueryVariablesFromContext($rewrite)
        );
    }

    public function invokeIsActiveCallback(RewriteInterface $rewrite)
    {
        $callback = $rewrite->getIsActiveCallback();

        if (null === $callback) {
            return true;
        }

        // @todo Should this get any additional context?
        return (bool) ($this->resolveCallable($callback))();
    }
}
