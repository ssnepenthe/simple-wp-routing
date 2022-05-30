<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use Invoker\Invoker;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ToyWpRouting\InvokerBackedInvocationStrategy;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteRule;

// @todo Test with custom resolver set on Invoker instance?
// @todo Move additional parameter tests to dedicated abstract invocation strategy test.
class InvokerBackedInvocationStrategyTest extends TestCase
{
    public function testInvokeHandler()
    {
        $invocationCount = 0;

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
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
        $invocationParam = '';

        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=$matches[1]')],
            function ($one) use (&$invocationCount, &$invocationParam) {
                $invocationCount++;
                $invocationParam = $one;

                return 'returnvalue';
            }
        );

        $returnValue = (new InvokerBackedInvocationStrategy(new Invoker()))
            ->withAdditionalContext(['queryVars' => ['one' => 'testvalue']])
            ->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame('testvalue', $invocationParam);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithCallableResolver()
    {
        $invocationCount = 0;
        $handler = function () use (&$invocationCount) {
            $invocationCount++;
        };

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
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

    public function testInvokeHandlerWithContainerBackedInvoker()
    {
        $container = new class () implements ContainerInterface {
            public $invocationCount = 0;

            public function get($name)
            {
                if ('testhandler' === $name) {
                    return function () {
                        $this->invocationCount++;

                        return 'returnvalue';
                    };
                }
            }

            public function has($name)
            {
                return 'testhandler' === $name;
            }
        };

        $strategy = new InvokerBackedInvocationStrategy(new Invoker(null, $container));
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=$matches[1]')],
            'testhandler'
        );

        $returnValue = $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $container->invocationCount);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithPrefixedAdditionalParameters()
    {
        $invocationCount = 0;
        $invocationParam = [];

        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=$matches[1]', 'pfx_')],
            function ($one) use (&$invocationCount, &$invocationParam) {
                $invocationCount++;
                $invocationParam = $one;

                return 'returnvalue';
            }
        );

        $returnValue  = (new InvokerBackedInvocationStrategy(new Invoker()))
            ->withAdditionalContext(['queryVars' => ['pfx_one' => 'testvalue']])
            ->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame('testvalue', $invocationParam);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeIsActiveCallback()
    {
        $invocationCount = 0;

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
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

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
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

    public function testInvokeIsActiveCallbackWithContainerBackedInvoker()
    {
        $container = new class () implements ContainerInterface {
            public $invocationCount = 0;

            public function get($name)
            {
                if ('testisactivecallback' === $name) {
                    return function () {
                        $this->invocationCount++;

                        return false;
                    };
                }
            }

            public function has($name)
            {
                return 'testisactivecallback' === $name;
            }
        };

        $strategy = new InvokerBackedInvocationStrategy(new Invoker(null, $container));
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=$matches[1]')],
            'irrelevanthandler'
        );
        $rewrite->setIsActiveCallback('testisactivecallback');

        $this->assertFalse($strategy->invokeIsActiveCallback($rewrite));
        $this->assertSame(1, $container->invocationCount);
    }

    public function testInvokeIsActiveCallbackWithNoCallbackSet()
    {
        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
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

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
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
