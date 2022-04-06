<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\Rewrite;

class RewriteTest extends TestCase
{
    public function testGetters()
    {
        $rewrite = new Rewrite(['GET'], ['someregex' => ['var' => 'value']], 'somehandler');

        $this->assertSame(['someregex' => 'index.php?var=value'], $rewrite->getRules());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertSame(['var'], $rewrite->getQueryVariables());
        $this->assertSame(['var' => 'var'], $rewrite->getPrefixedToUnprefixedQueryVariablesMap());
        $this->assertNull($rewrite->getIsActiveCallback());
    }

    public function testMethodsAreUppercased()
    {
        $rewrite = new Rewrite(['get'], ['someregex' => ['var' => 'value']], 'somehandler');

        $this->assertSame(['GET'], $rewrite->getMethods());
    }

    public function testMultipleMethodsAndRules()
    {
        $rewrite = new Rewrite(
            ['GET', 'HEAD', 'POST'],
            [
                'someregex' => ['var' => 'value'],
                'anotherregex' => ['anothervar' => 'anothervalue'],
            ],
            'somehandler'
        );

        $this->assertSame([
            'someregex' => 'index.php?var=value',
            'anotherregex' => 'index.php?anothervar=anothervalue'
        ], $rewrite->getRules());
        $this->assertSame(['GET', 'HEAD', 'POST'], $rewrite->getMethods());
        $this->assertSame(['var', 'anothervar'], $rewrite->getQueryVariables());
        $this->assertSame([
            'var' => 'var',
            'anothervar' => 'anothervar',
        ], $rewrite->getPrefixedToUnprefixedQueryVariablesMap());
    }

    public function testWithIsActiveCallback()
    {
        $rewrite = new Rewrite(
            ['GET'],
            ['someregex' => ['var' => 'value']],
            'somehandler',
            '',
            'someisactivecallback'
        );

        $this->assertSame('someisactivecallback', $rewrite->getIsActiveCallback());
    }

    public function testWithPrefix()
    {
        $rewrite = new Rewrite(['GET'], ['someregex' => ['var' => 'value']], 'somehandler', 'pfx_');

        $this->assertSame(['someregex' => 'index.php?pfx_var=value'], $rewrite->getRules());
        $this->assertSame(['pfx_var'], $rewrite->getQueryVariables());
        $this->assertSame(['pfx_var' => 'var'], $rewrite->getPrefixedToUnprefixedQueryVariablesMap());
    }
}
