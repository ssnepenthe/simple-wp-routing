<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\InvocationStrategy;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\InvocationStrategy\InvokerBackedInvocationStrategy;

// @todo Test with custom resolver set on Invoker instance?
class InvokerBackedInvocationStrategyTest extends TestCase
{
    public function testInvoke()
    {
        $invocationStrategy = new InvokerBackedInvocationStrategy();

        $this->assertSame('testreturnval', $invocationStrategy->invoke(fn () => 'testreturnval'));
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
