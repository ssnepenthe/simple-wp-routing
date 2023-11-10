<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Dumper;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Dumper\OptimizedRewrite;

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
    }

    public function testSetIsActiveCallback()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot override isActiveCallback');

        $rewrite = new OptimizedRewrite(['GET'], 'regex', 'query', ['some' => 'some'], 'somehandler');
        $rewrite->setIsActiveCallback('someisactivecallback');
    }
}
