<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\InvocationStrategy;

use PHPUnit\Framework\TestCase;
use SimpleWpRouting\InvocationStrategy\InvokerBackedInvocationStrategy;

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
