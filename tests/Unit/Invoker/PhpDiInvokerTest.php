<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\InvocationStrategy;

use PHPUnit\Framework\TestCase;
use SimpleWpRouting\Invoker\PhpDiInvoker;

class PhpDiInvokerTest extends TestCase
{
    public function testInvoke()
    {
        $invocationStrategy = new PhpDiInvoker();

        $this->assertSame('testreturnval', $invocationStrategy->invoke(fn () => 'testreturnval'));
    }

    public function testInvokeWithContext()
    {
        $invocationStrategy = new PhpDiInvoker();

        $this->assertSame(
            'testreturnval',
            $invocationStrategy->invoke(fn ($append) => 'test' . $append, ['returnval'])
        );
    }
}
