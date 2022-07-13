<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\DefaultInvocationStrategy;

// @todo Move additional parameter tests to dedicated abstract invocation strategy test.
class DefaultInvocationStrategyTest extends TestCase
{
    use CreatesRewriteStubs;

    public function testInvokeHandler()
    {
        $invocationCount = 0;

        $strategy = new DefaultInvocationStrategy();

        $rewrite = $this->createRewriteStub([
            'handler' => function () use (&$invocationCount) {
                $invocationCount++;

                return 'returnvalue';
            },
        ]);

        $returnValue = $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithAdditionalParameters()
    {
        $invocationCount = 0;
        $invocationParams = [];

        $rewrite = $this->createRewriteStub([
            'handler' => function (array $params) use (&$invocationCount, &$invocationParams) {
                $invocationCount++;
                $invocationParams = $params;

                return 'returnvalue';
            },
            'qvMap' => ['one' => 'one'],
        ]);

        $returnValue = (new DefaultInvocationStrategy())
            ->withAdditionalContext(['queryVars' => ['one' => 'testvalue']])
            ->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame(['one' => 'testvalue'], $invocationParams);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithCallableResolver()
    {
        $invocationCount = 0;
        $handler = function () use (&$invocationCount) {
            $invocationCount++;
        };

        $strategy = new DefaultInvocationStrategy();
        $strategy->setCallableResolver(function ($potentialCallable) use ($handler) {
            if ('handler' === $potentialCallable) {
                return $handler;
            }

            return $potentialCallable;
        });

        $rewrite = $this->createRewriteStub([
            'handler' => 'handler',
        ]);

        $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
    }

    public function testInvokeHandlerWithPrefixedAdditionalParameters()
    {
        $invocationCount = 0;
        $invocationParams = [];

        $rewrite = $this->createRewriteStub([
            'handler' => function (array $params) use (&$invocationCount, &$invocationParams) {
                $invocationCount++;
                $invocationParams = $params;

                return 'returnvalue';
            },
            'qvMap' => ['pfx_one' => 'one'],
        ]);

        $returnValue = (new DefaultInvocationStrategy())
            ->withAdditionalContext(['queryVars' => ['pfx_one' => 'testvalue']])
            ->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame(['one' => 'testvalue'], $invocationParams);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeIsActiveCallback()
    {
        $invocationCount = 0;

        $strategy = new DefaultInvocationStrategy();
        $one = $this->createRewriteStub([
            'isActive' => function () use (&$invocationCount) {
                $invocationCount++;

                return true;
            },
        ]);
        $two = $this->createRewriteStub([
            'isActive' => function () use (&$invocationCount) {
                $invocationCount++;

                return false;
            },
        ]);

        $this->assertTrue($strategy->invokeIsActiveCallback($one));
        $this->assertFalse($strategy->invokeIsActiveCallback($two));
        $this->assertSame(2, $invocationCount);
    }

    public function testInvokeIsActiveCallbackWithCallableResolver()
    {
        $callableResolverInvocationCount = 0;
        $isActiveCallbackInvocationCount = 0;

        $isActiveCallback = function () use (&$isActiveCallbackInvocationCount) {
            $isActiveCallbackInvocationCount++;
        };

        $strategy = new DefaultInvocationStrategy();
        $strategy->setCallableResolver(
            function ($potentialCallable) use ($isActiveCallback, &$callableResolverInvocationCount) {
                $callableResolverInvocationCount++;

                if ('isactivecallback' === $potentialCallable) {
                    return $isActiveCallback;
                }

                return $potentialCallable;
            }
        );

        $one = $this->createRewriteStub();
        $two = $this->createRewriteStub([
            'isActive' => 'isactivecallback',
        ]);

        $strategy->invokeIsActiveCallback($one);

        // Not invoked when no callback is set.
        $this->assertSame(0, $callableResolverInvocationCount);
        $this->assertSame(0, $isActiveCallbackInvocationCount);

        $strategy->invokeIsActiveCallback($two);

        $this->assertSame(1, $callableResolverInvocationCount);
        $this->assertSame(1, $isActiveCallbackInvocationCount);
    }

    public function testInvokeIsActiveCallbackWithNoCallbackSet()
    {
        $strategy = new DefaultInvocationStrategy();
        $rewrite = $this->createRewriteStub([
            'isActive' => null,
        ]);

        $isActive = $strategy->invokeIsActiveCallback($rewrite);

        $this->assertTrue($isActive);
    }

    public function testInvokeIsActiveCallbackWithNonBooleanReturnValue()
    {
        $invocationCount = 0;

        $strategy = new DefaultInvocationStrategy();
        $one = $this->createRewriteStub([
            'isActive' => function () use (&$invocationCount) {
                $invocationCount++;

                return 1;
            },
        ]);
        $two = $this->createRewriteStub([
            'isActive' => function () use (&$invocationCount) {
                $invocationCount++;

                return '';
            },
        ]);

        $this->assertTrue($strategy->invokeIsActiveCallback($one));
        $this->assertFalse($strategy->invokeIsActiveCallback($two));
        $this->assertSame(2, $invocationCount);
    }
}
