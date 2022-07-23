<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\DefaultInvocationStrategy;
use ToyWpRouting\InvocationStrategyInterface;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteRule;

class RewriteTest extends TestCase
{
    public function testGetInvocationStrategyDefault()
    {
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('someregex', 'some=query')],
            'somehandler'
        );

        $this->assertInstanceOf(
            DefaultInvocationStrategy::class,
            $rewrite->getInvocationStrategy()
        );
    }
    public function testGetters()
    {
        $rules = [new RewriteRule('someregex', 'index.php?var=value')];

        $rewrite = new Rewrite(['GET'], $rules, 'somehandler');

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertNull($rewrite->getIsActiveCallback());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame($rules, $rewrite->getRules());
    }

    public function testHandle()
    {
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('someregex', 'some=query')],
            'somehandler'
        );

        $invocationStrategy = $this->createMock(InvocationStrategyInterface::class);
        $invocationStrategy->expects($this->once())
            ->method('withAdditionalContext')
            ->with(['queryVars' => ['varone' => 'valone', 'vartwo' => 'valtwo']])
            ->willReturn($invocationStrategy);
        $invocationStrategy->expects($this->once())
            ->method('invokeHandler')
            ->with($rewrite);

        $rewrite->setInvocationStrategy($invocationStrategy);

        $rewrite->handle(['varone' => 'valone', 'vartwo' => 'valtwo']);
    }

    public function testIsActive()
    {
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('someregex', 'some=query')],
            'somehandler',
        );

        $invocationStrategy = $this->createMock(InvocationStrategyInterface::class);
        $invocationStrategy->expects($this->once())
            ->method('invokeIsActiveCallback')
            ->with($rewrite)
            ->willReturn(true);

        $rewrite->setInvocationStrategy($invocationStrategy);

        $rewrite->isActive();
    }

    public function testMapQueryVariable()
    {
        $rewrite = new Rewrite(['GET'], [
            new RewriteRule('regexone', 'one=valone'),
            new RewriteRule('regextwo', 'two=valtwo', 'pfx_'),
        ], 'somehandler');

        $this->assertSame('one', $rewrite->mapQueryVariable('one'));
        $this->assertSame('two', $rewrite->mapQueryVariable('pfx_two'));
        $this->assertNull($rewrite->mapQueryVariable('two'));
        $this->assertNull($rewrite->mapQueryVariable('three'));
    }

    public function testWithInvalidMethods()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@todo');

        new Rewrite(
            ['GET', 'BADMETHOD'],
            [new RewriteRule('someregex', 'some=query')],
            'somehandler'
        );
    }

    public function testWithIsActiveCallback()
    {
        $one = new Rewrite(
            ['GET'],
            [new RewriteRule('someregex', 'index.php?var=value')],
            'somehandler',
        );
        $one->setIsActiveCallback('someisactivecallback');

        $two = new Rewrite(
            ['GET'],
            [new RewriteRule('anotherregex', 'index.php?var=value')],
            'anotherhandler',
            'anotherisactivecallback'
        );

        $this->assertSame('someisactivecallback', $one->getIsActiveCallback());
        $this->assertSame('anotherisactivecallback', $two->getIsActiveCallback());
    }
}
