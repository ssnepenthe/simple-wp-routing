<?php

declare(strict_types=1);

namespace SimpleWpRouting\Invoker;

use Invoker\Invoker;
use Invoker\InvokerInterface as PhpDiInvokerInterface;

/**
 * Passthru to the php-di/invoker package - must be installed separately.
 */
final class PhpDiInvoker implements InvokerInterface
{
    private PhpDiInvokerInterface $invoker;

    public function __construct(?PhpDiInvokerInterface $invoker = null)
    {
        $this->invoker = $invoker ?: new Invoker();
    }

    /**
     * @return mixed
     */
    public function invoke(callable $callable, array $context = [])
    {
        return $this->invoker->call($callable, $context);
    }
}
