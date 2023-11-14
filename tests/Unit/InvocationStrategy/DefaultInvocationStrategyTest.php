<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\InvocationStrategy;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\InvocationStrategy\DefaultInvocationStrategy;

class DefaultInvocationStrategyTest extends TestCase
{
    public function testInvoke()
    {
        $invocationStrategy = new DefaultInvocationStrategy();

        $this->assertSame('testreturnval', $invocationStrategy->invoke(fn () => 'testreturnval'));
    }

    public function testInvokeWithContext()
    {
        $invocationStrategy = new DefaultInvocationStrategy();

        $this->assertSame(
            'testreturnval',
            $invocationStrategy->invoke(
                fn ($params) => 'test' . $params['append'],
                ['append' => 'returnval']
            )
        );
    }
}
