<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\DefaultInvocationStrategy;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteRule;

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

        $strategy = new DefaultInvocationStrategy();
        $strategy->withAdditionalContext(['queryVars' => ['one' => 'testvalue']]);
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=$matches[1]')],
            function (array $params) use (&$invocationCount, &$invocationParams) {
                $invocationCount++;
                $invocationParams = $params;

                return 'returnvalue';
            }
        );

        $returnValue = $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame(['one' => 'testvalue'], $invocationParams);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithPrefixedAdditionalParameters()
    {
        $invocationCount = 0;
        $invocationParams = [];

        $strategy = new DefaultInvocationStrategy();
        $strategy->withAdditionalContext(['queryVars' => ['pfx_one' => 'testvalue']]);
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=$matches[1]', 'pfx_')],
            function (array $params) use (&$invocationCount, &$invocationParams) {
                $invocationCount++;
                $invocationParams = $params;

                return 'returnvalue';
            }
        );

        $returnValue  = $strategy->invokeHandler($rewrite);

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

    public function testInvokeIsActiveCallbackWithNonCallableCallback()
    {
        $this->expectException(InvalidArgumentException::class);

        $strategy = new DefaultInvocationStrategy();
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('^one$', 'index.php?one=one')],
            'irrelevanthandler'
        );
        $rewrite->setIsActiveCallback('noncallablevalue');

        $strategy->invokeIsActiveCallback($rewrite);
    }
}
