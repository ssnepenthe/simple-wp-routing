<?php

namespace ToyWpRouting;

use InvalidArgumentException;
use Invoker\InvokerInterface;

abstract class AbstractRewrite implements RewriteInterface
{
    protected $isActiveCallback;

    public function getIsActiveCallback()
    {
        return $this->isActiveCallback;
    }

    public function isActive(?InvokerInterface $invoker = null): bool
    {
        if (null === $this->isActiveCallback) {
            return true;
        }

        if (null !== $invoker) {
            return (bool) $invoker->call($this->isActiveCallback);
        }

        if (! is_callable($this->isActiveCallback)) {
            throw new InvalidArgumentException(
                "Invalid isActiveCallback for rewrite {$this->getMethod()}:{$this->getRegex()}"
            );
        }

        return (bool) ($this->isActiveCallback)();
    }

    public function setIsActiveCallback($isActiveCallback)
    {
        $this->isActiveCallback = $isActiveCallback;
    }
}
