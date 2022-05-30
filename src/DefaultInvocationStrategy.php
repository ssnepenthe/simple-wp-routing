<?php

declare(strict_types=1);

namespace ToyWpRouting;

use InvalidArgumentException;

class DefaultInvocationStrategy extends AbstractInvocationStrategy
{
    public function invokeHandler(RewriteInterface $rewrite)
    {
        // @todo Should this receive full additional context as second param?
        return ($rewrite->getHandler())(
            $this->resolveRelevantQueryVariablesFromContext($rewrite)
        );
    }

    public function invokeIsActiveCallback(RewriteInterface $rewrite)
    {
        $callback = $rewrite->getIsActiveCallback();

        if (null === $callback) {
            return true;
        }

        if (! is_callable($callback)) {
            throw new InvalidArgumentException('@todo');
        }

        // @todo Should this get any additional context?
        return (bool) $callback();
    }
}
