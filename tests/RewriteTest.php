<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteRule;

class RewriteTest extends TestCase
{
    public function testGetters()
    {
        $rules = [new RewriteRule('someregex', 'index.php?var=value')];

        $rewrite = new Rewrite(['GET'], $rules, 'somehandler');

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertNull($rewrite->getIsActiveCallback());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame($rules, $rewrite->getRules());
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
