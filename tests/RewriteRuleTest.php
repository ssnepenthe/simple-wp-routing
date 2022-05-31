<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\RewriteRule;

class RewriteRuleTest extends TestCase
{
    public function testEmptyQuery()
    {
        $rule = new RewriteRule('someregex', '');

        $this->assertSame("index.php?matchedRule={$rule->getHash()}", $rule->getQuery());
    }

    public function testEmptyQueryWithPrefix()
    {
        $rule = new RewriteRule('someregex', '', 'pfx_');

        $this->assertSame("index.php?pfx_matchedRule={$rule->getHash()}", $rule->getQuery());
    }

    public function testGetters()
    {
        $rule = new RewriteRule('someregex', 'index.php?var=value');

        $this->assertSame(md5('someregex'), $rule->getHash());
        $this->assertSame([
            'var' => 'value',
            'matchedRule' => $rule->getHash(),
        ], $rule->getPrefixedQueryArray());
        $this->assertSame("index.php?var=value&matchedRule={$rule->getHash()}", $rule->getQuery());
        $this->assertSame([
            'var' => 'value',
            'matchedRule' => $rule->getHash(),
        ], $rule->getQueryArray());
    }

    public function testGettersWithPrefix()
    {
        $rule = new RewriteRule('someregex', 'index.php?var=value', 'pfx_');

        $this->assertSame(md5('someregex'), $rule->getHash());
        $this->assertSame([
            'pfx_var' => 'value',
            'pfx_matchedRule' => $rule->getHash(),
        ], $rule->getPrefixedQueryArray());
        $this->assertSame(
            "index.php?pfx_var=value&pfx_matchedRule={$rule->getHash()}",
            $rule->getQuery()
        );
        $this->assertSame([
            'var' => 'value',
            'matchedRule' => $rule->getHash(),
        ], $rule->getQueryArray());
    }

    public function testQueryWithoutLeadingIndexPhp()
    {
        $rule = new RewriteRule('someregex', 'var=value');

        $this->assertSame("index.php?var=value&matchedRule={$rule->getHash()}", $rule->getQuery());
    }

    public function testQueryWithoutLeadingIndexPhpAndWithPrefix()
    {
        $rule = new RewriteRule('someregex', 'var=value', 'pfx_');

        $this->assertSame(
            "index.php?pfx_var=value&pfx_matchedRule={$rule->getHash()}",
            $rule->getQuery()
        );
    }
}
