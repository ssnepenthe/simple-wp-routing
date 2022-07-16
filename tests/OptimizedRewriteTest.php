<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\OptimizedRewrite;
use ToyWpRouting\RewriteRule;

class OptimizedRewriteTest extends TestCase
{
    public function testGetters()
    {
        $rewrite = new OptimizedRewrite(
            ['GET'],
            $rules = [new RewriteRule('someregex', 'index.php?var=value', 'pfx_')],
            ['pfx_var' => 'var'],
            'somehandler',
            'isActiveCallback'
        );

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertSame('isActiveCallback', $rewrite->getIsActiveCallback());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame($rules, $rewrite->getRules());
    }

    public function testMapQueryVariable()
    {
        $rewrite = new OptimizedRewrite(
            ['GET'],
            [
                new RewriteRule('someregex', 'some=query'),
                new RewriteRule('anotherregex', 'another=query', 'pfx_')
            ],
            [
                'some' => 'some',
                'pfx_another' => 'another',
            ],
            'somehandler',
            'isActiveCallback'
        );

        $this->assertSame('some', $rewrite->mapQueryVariable('some'));
        $this->assertSame('another', $rewrite->mapQueryVariable('pfx_another'));
        $this->assertNull($rewrite->mapQueryVariable('another'));
        $this->assertNull($rewrite->mapQueryVariable('andanother'));
    }

    public function testSetIsActiveCallback()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot override isActiveCallback');

        $rewrite = new OptimizedRewrite(
            ['GET'],
            [new RewriteRule('someregex', 'some=query')],
            ['some' => 'some'],
            'somehandler'
        );
        $rewrite->setIsActiveCallback('someisactivecallback');
    }
}
