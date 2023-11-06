<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Compiler\OptimizedRewrite;

class OptimizedRewriteTest extends TestCase
{
    public function testGetters()
    {
        $rewrite = new OptimizedRewrite(
            ['GET'],
            'regex',
            'query',
            ['pfx_var' => 'var'],
            'somehandler',
            'isActiveCallback'
        );

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertSame('isActiveCallback', $rewrite->getIsActiveCallback());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame(['pfx_var'], $rewrite->getRequiredQueryVariables());
    }

    public function testMapQueryVariable()
    {
        $rewrite = new OptimizedRewrite(
            ['GET'],
            'regex',
            'query',
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

        $rewrite = new OptimizedRewrite(['GET'], 'regex', 'query', ['some' => 'some'], 'somehandler');
        $rewrite->setIsActiveCallback('someisactivecallback');
    }
}
