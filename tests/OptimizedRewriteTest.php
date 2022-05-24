<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\OptimizedRewrite;
use ToyWpRouting\RewriteRule;

class OptimizedRewriteTest extends TestCase
{
    public function testGetters()
    {
        $rewrite = new OptimizedRewrite(
            ['GET'],
            ['someregex' => 'index.php?pfx_var=value'],
            $rules = [new RewriteRule('someregex', 'index.php?var=value', 'pfx_')],
            'somehandler',
            ['pfx_var' => 'var'],
            ['pfx_var'],
            'isActiveCallback'
        );

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertSame('isActiveCallback', $rewrite->getIsActiveCallback());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame(
            ['pfx_var' => 'var'],
            $rewrite->getPrefixedToUnprefixedQueryVariablesMap()
        );
        $this->assertSame(['pfx_var'], $rewrite->getQueryVariables());
        $this->assertSame(['someregex' => 'index.php?pfx_var=value'], $rewrite->getRewriteRules());
        $this->assertSame($rules, $rewrite->getRules());
    }
}
