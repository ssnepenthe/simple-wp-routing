<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\DefaultInvocationStrategy;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteRule;

// @todo Move additional parameter tests to dedicated abstract invocation strategy test.
class DefaultInvocationStrategyTest extends TestCase
{
    public function testInvokeHandler()
    {
        $invocationCount = 0;

        $strategy = new DefaultInvocationStrategy();
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            function () use (&$invocationCount) {
                $invocationCount++;

                return 'returnvalue';
            }
        );

        $returnValue = $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithAdditionalParameters()
    {
        $invocationCount = 0;
        $invocationParams = [];

        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=$matches[1]')],
            function (array $params) use (&$invocationCount, &$invocationParams) {
                $invocationCount++;
                $invocationParams = $params;

                return 'returnvalue';
            }
        );

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

        $rewrite = new Rewrite(['GET'], [new RewriteRule('^one$', 'index.php?one=one')], 'handler');

        $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
    }

    public function testInvokeHandlerWithPrefixedAdditionalParameters()
    {
        $invocationCount = 0;
        $invocationParams = [];

        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=$matches[1]', 'pfx_')],
            function (array $params) use (&$invocationCount, &$invocationParams) {
                $invocationCount++;
                $invocationParams = $params;

                return 'returnvalue';
            }
        );

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
        $one = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            'irrelevanthandler'
        );
        $one->setIsActiveCallback(function () use (&$invocationCount) {
            $invocationCount++;

            return true;
        });
        $two = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            'irrelevanthandler'
        );
        $two->setIsActiveCallback(function () use (&$invocationCount) {
            $invocationCount++;

            return false;
        });

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

        $one = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            'irrelevanthandler'
        );
        $two = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            'irrelevanthandler'
        );
        $two->setIsActiveCallback('isactivecallback');

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
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            'irrelevanthandler'
        );

        $isActive = $strategy->invokeIsActiveCallback($rewrite);

        $this->assertTrue($isActive);
    }

    public function testInvokeIsActiveCallbackWithNonBooleanReturnValue()
    {
        $invocationCount = 0;

        $strategy = new DefaultInvocationStrategy();
        $one = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            'irrelevanthandler'
        );
        $one->setIsActiveCallback(function () use (&$invocationCount) {
            $invocationCount++;

            return 1;
        });
        $two = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            'irrelevanthandler'
        );
        $two->setIsActiveCallback(function () use (&$invocationCount) {
            $invocationCount++;

            return '';
        });

        $this->assertTrue($strategy->invokeIsActiveCallback($one));
        $this->assertFalse($strategy->invokeIsActiveCallback($two));
        $this->assertSame(2, $invocationCount);
    }
}
