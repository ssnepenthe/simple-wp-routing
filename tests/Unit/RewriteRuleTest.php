<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\RewriteRule;

class RewriteRuleTest extends TestCase
{
    public function testEmptyQuery()
    {
        $rule = new RewriteRule('someregex', '');

        $this->assertSame('index.php?__routeType=static', $rule->getQuery());
    }

    public function testEmptyQueryWithPrefix()
    {
        $rule = new RewriteRule('someregex', '', 'pfx_');

        $this->assertSame('index.php?pfx___routeType=static', $rule->getQuery());
    }

    public function testGetters()
    {
        $rule = new RewriteRule('someregex', 'index.php?var=value');

        $this->assertSame('index.php?var=value', $rule->getQuery());
        $this->assertSame(['var' => 'var'], $rule->getQueryVariables());
    }

    public function testGettersWithPrefix()
    {
        $rule = new RewriteRule('someregex', 'index.php?var=value', 'pfx_');

        $this->assertSame('index.php?pfx_var=value', $rule->getQuery());
        $this->assertSame(['pfx_var' => 'var'], $rule->getQueryVariables());
    }

    public function testQueryWithoutLeadingIndexPhp()
    {
        $rule = new RewriteRule('someregex', 'var=value');

        $this->assertSame('index.php?var=value', $rule->getQuery());
    }

    public function testQueryWithoutLeadingIndexPhpAndWithPrefix()
    {
        $rule = new RewriteRule('someregex', 'var=value', 'pfx_');

        $this->assertSame('index.php?pfx_var=value', $rule->getQuery());
    }
}
