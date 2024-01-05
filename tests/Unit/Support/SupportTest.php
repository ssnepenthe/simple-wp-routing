<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\Support;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleWpRouting\Support\Support;

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

    public function testAssertValidMethodsListWithEmptyMethods()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be empty');

        Support::assertValidMethodsList([]);
    }

    public function testAssertValidMethodsListWithInvalidMethods()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('the following values are not allowed: \'get\', \'INVALID\'');

        Support::assertValidMethodsList(['GET', 'get', 'INVALID']);
    }

    public function testAssertValidMethodsListWithInvalidTypes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain only strings');

        Support::assertValidMethodsList(['GET', 1, []]);
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
}
