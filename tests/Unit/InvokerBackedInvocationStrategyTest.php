<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\InvokerBackedInvocationStrategy;

// @todo Test with custom resolver set on Invoker instance?
class InvokerBackedInvocationStrategyTest extends TestCase
{
    public function testInvoke()
    {
        $invocationStrategy = new InvokerBackedInvocationStrategy();

        $this->assertSame('testreturnval', $invocationStrategy->invoke(fn () => 'testreturnval'));
    }

    public function testInvokeWithCallableResolver()
    {
        $invocationStrategy = new InvokerBackedInvocationStrategy();
        $invocationStrategy->setCallableResolver(function ($potentialCallable) {
            if ('handler' === $potentialCallable) {
                return fn () => 'modified';
            }

            return $potentialCallable;
        });

        $this->assertSame('modified', $invocationStrategy->invoke('handler'));
    }

    public function testInvokeWithContext()
    {
        $invocationStrategy = new InvokerBackedInvocationStrategy();

        $this->assertSame(
            'testreturnval',
            $invocationStrategy->invoke(fn ($append) => 'test' . $append, ['returnval'])
        );
    }
}
