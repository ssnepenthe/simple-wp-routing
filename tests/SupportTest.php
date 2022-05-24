<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\Support;

class SupportTest extends TestCase
{
    public function testApplyPrefix()
    {
        $value = 'irrelevant';
        $prefix = 'pfx_';

        $this->assertSame('pfx_irrelevant', Support::applyPrefix($value, $prefix));
    }

    public function testApplyPrefixToKeys()
    {
        $value = ['one' => 'two', 'three' => 'four', 'five' => 'six'];
        $prefix = 'pfx_';

        $this->assertSame(
            ['pfx_one' => 'two', 'pfx_three' => 'four', 'pfx_five' => 'six'],
            Support::applyPrefixToKeys($value, $prefix)
        );
    }

    public function testApplyPrefixWhenStringIsAlreadyPrefixed()
    {
        $prefixedValue = 'pfx_irrelevant';
        $prefix = 'pfx_';

        $this->assertSame('pfx_irrelevant', Support::applyPrefix($prefixedValue, $prefix));
    }

    // @todo test with empty input
    public function testBuildQuery()
    {
        $queryArray = [
            'one' => 'two',
            'three' => 'four',
            'five' => 'six',
        ];

        $this->assertSame(
            'index.php?one=two&three=four&five=six',
            Support::buildQuery($queryArray)
        );
    }

    public function testParseQuery()
    {
        $query = 'index.php?one=two&three=four&five=six';

        $this->assertSame([
            'one' => 'two',
            'three' => 'four',
            'five' => 'six',
        ], Support::parseQuery($query));
    }

    public function testParseQueryWithoutLeadingIndexPhp()
    {
        $query = 'one=two&three=four&five=six';

        $this->assertSame([
            'one' => 'two',
            'three' => 'four',
            'five' => 'six',
        ], Support::parseQuery($query));
    }
}
