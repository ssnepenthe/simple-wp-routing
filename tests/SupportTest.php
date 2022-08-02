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

    public function testApplyPrefixToKeysWithEmptyPrefix()
    {
        // @todo Ideally we would be able to test not just that this works but that it isbailing early.
        $value = ['one' => 'two', 'three' => 'four', 'five' => 'six'];
        $prefix = '';

        $this->assertSame($value, Support::applyPrefixToKeys($value, $prefix));
    }

    public function testApplyPrefixWhenStringIsAlreadyPrefixed()
    {
        $prefixedValue = 'pfx_irrelevant';
        $prefix = 'pfx_';

        $this->assertSame('pfx_irrelevant', Support::applyPrefix($prefixedValue, $prefix));
    }

    public function testApplyPrefixWithEmptyPrefix()
    {
        // @todo Ideally we would be able to test not just that this works but that it isbailing early.
        $value = 'irrelevant';
        $prefix = '';

        $this->assertSame($value, Support::applyPrefix($value, $prefix));
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

    public function testClassBaseName()
    {
        $this->assertSame('Baz', Support::classBaseName('Baz'));
        $this->assertSame('Baz', Support::classBaseName('Bar\\Baz'));
        $this->assertSame('Baz', Support::classBaseName('Foo\\Bar\\Baz'));
    }

    public function testClassUsesRecursive()
    {
        $this->assertSame([
            TraitTwo::class => TraitTwo::class,
            TraitOne::class => TraitOne::class,
        ], Support::classUsesRecursive(ClassTwo::class));

        $this->assertSame([
            TraitTwo::class => TraitTwo::class,
            TraitOne::class => TraitOne::class,
            TraitThree::class => TraitThree::class,
        ], Support::classUsesRecursive(ClassThree::class));
    }

    public function testIsValidMethodsList()
    {
        // False for empty array.
        $this->assertFalse(Support::isValidMethodsList([]));

        // Full list of valid methods.
        $this->assertTrue(Support::isValidMethodsList(
            ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT']
        ));
        // Subset of list.
        $this->assertTrue(Support::isValidMethodsList(['GET', 'POST', 'PUT']));

        // False for lowercase methods.
        $this->assertFalse(Support::isValidMethodsList(
            ['delete', 'get', 'head', 'options', 'patch', 'post', 'put']
        ));
        $this->assertFalse(Support::isValidMethodsList(['get', 'post', 'put']));
        // False for full list with any additional.
        $this->assertFalse(Support::isValidMethodsList(
            ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT', 'NONSENSE']
        ));
        // False for subset of list with additional.
        $this->assertFalse(Support::isValidMethodsList(['GET', 'POST', 'PUT', 'IRRELEVANT']));
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

trait TraitOne
{
    //
}

trait TraitTwo
{
    use TraitOne;
}

trait TraitThree
{
    //
}

class ClassOne
{
    use TraitTwo;
}

class ClassTwo extends ClassOne
{
    //
}

class ClassThree extends ClassTwo
{
    use TraitThree;
}
