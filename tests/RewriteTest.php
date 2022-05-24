<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

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
        $this->assertSame(
            ['var' => 'var', 'matchedRule' => 'matchedRule'],
            $rewrite->getPrefixedToUnprefixedQueryVariablesMap()
        );
        $this->assertSame(['var', 'matchedRule'], $rewrite->getQueryVariables());
        $this->assertSame(
            ['someregex' => "index.php?var=value&matchedRule={$rules[0]->getHash()}"],
            $rewrite->getRewriteRules()
        );
        $this->assertSame($rules, $rewrite->getRules());
    }

    public function testGettersWithPrefixedRules()
    {
        $rules = [new RewriteRule('someregex', 'index.php?var=value', 'pfx_')];

        $rewrite = new Rewrite(['GET'], $rules, 'somehandler');

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertNull($rewrite->getIsActiveCallback());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame(
            ['pfx_var' => 'var', 'pfx_matchedRule' => 'matchedRule'],
            $rewrite->getPrefixedToUnprefixedQueryVariablesMap()
        );
        $this->assertSame(['pfx_var', 'pfx_matchedRule'], $rewrite->getQueryVariables());
        $this->assertSame(
            ['someregex' => "index.php?pfx_var=value&pfx_matchedRule={$rules[0]->getHash()}"],
            $rewrite->getRewriteRules()
        );
        $this->assertSame($rules, $rewrite->getRules());
    }

    public function testMethodsAreUppercasedByDefault()
    {
        $rewrite = new Rewrite(['get'], [], 'somehandler');

        $this->assertSame(['GET'], $rewrite->getMethods());
    }

    public function testMultipleMethodsAndRules()
    {
        $rules = [
            new RewriteRule('someregex', 'index.php?var=value'),
            new RewriteRule('anotherregex', 'index.php?anothervar=anothervalue'),
        ];
        $rewrite = new Rewrite(['GET', 'HEAD', 'POST'], $rules, 'somehandler');

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertNull($rewrite->getIsActiveCallback());
        $this->assertSame(['GET', 'HEAD', 'POST'], $rewrite->getMethods());
        $this->assertSame([
            'var' => 'var',
            'matchedRule' => 'matchedRule',
            'anothervar' => 'anothervar',
        ], $rewrite->getPrefixedToUnprefixedQueryVariablesMap());
        $this->assertSame(['var', 'matchedRule', 'anothervar'], $rewrite->getQueryVariables());
        $this->assertSame([
            'someregex' => "index.php?var=value&matchedRule={$rules[0]->getHash()}",
            'anotherregex' => "index.php?anothervar=anothervalue&matchedRule={$rules[1]->getHash()}"
        ], $rewrite->getRewriteRules());
        $this->assertSame($rules, $rewrite->getRules());
    }

    public function testWithIsActiveCallback()
    {
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('someregex', 'index.php?var=value')],
            'somehandler',
        );

        $rewrite->setIsActiveCallback('someisactivecallback');

        $this->assertSame('someisactivecallback', $rewrite->getIsActiveCallback());
    }
}
