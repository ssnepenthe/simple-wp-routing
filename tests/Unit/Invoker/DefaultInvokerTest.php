<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\InvocationStrategy;

use PHPUnit\Framework\TestCase;
use SimpleWpRouting\Invoker\DefaultInvoker;

class DefaultInvokerTest extends TestCase
{
    public function testInvoke()
    {
        $invocationStrategy = new DefaultInvoker();

        $this->assertSame('testreturnval', $invocationStrategy->invoke(fn () => 'testreturnval'));
    }

    public function testInvokeWithContext()
    {
        $invocationStrategy = new DefaultInvoker();

        $this->assertSame(
            'testreturnval',
            $invocationStrategy->invoke(
                fn ($params) => 'test' . $params['append'],
                ['append' => 'returnval']
            )
        );
    }
}
