<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\Exception\RequiredQueryVariablesMissingException;
use ToyWpRouting\Rewrite;

class RewriteTest extends TestCase
{
    public function testGetters()
    {
        $rewrite = new Rewrite(['GET'], 'someregex', 'index.php?var=value', ['var' => 'var'], 'somehandler');

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame('someregex', $rewrite->getRegex());
        $this->assertSame('index.php?var=value', $rewrite->getQuery());
        $this->assertSame(['var' => 'var'], $rewrite->getQueryVariables());

        $this->assertFalse($rewrite->hasIsActiveCallback());
        $this->assertInstanceOf(Closure::class, $rewrite->getIsActiveCallback());

        $isActiveCallback = 'irrelevant';

        $rewrite->setIsActiveCallback($isActiveCallback);

        $this->assertTrue($rewrite->hasIsActiveCallback());
        $this->assertSame($isActiveCallback, $rewrite->getIsActiveCallback());
    }

    public function testCreateWithPrefix()
    {
        $rewrite = new Rewrite(['GET'], 'someregex', 'index.php?pfx_var=value', ['pfx_var' => 'var'], 'somehandler');

        $this->assertSame('index.php?pfx_var=value', $rewrite->getQuery());
        $this->assertSame(['pfx_var' => 'var'], $rewrite->getQueryVariables());
    }

    public function testGetConcernedQueryVariablesWithoutPrefix()
    {
        // Without prefix.
        $rewrite = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', ['one' => 'one', 'two' => 'two'], 'somehandler');

        $this->assertSame(
            ['one' => 'valone', 'two' => 'valtwo'],
            $rewrite->getConcernedQueryVariablesWithoutPrefix(['one' => 'valone', 'two' => 'valtwo', 'three' => 'valthree'])
        );

        // With prefix.
        $rewrite = new Rewrite(['GET'], 'regex', 'pfx_one=valone&pfx_two=valtwo', ['pfx_one' => 'one', 'pfx_two' => 'two'], 'somehandler');

        $this->assertSame(
            ['one' => 'valone', 'two' => 'valtwo'],
            $rewrite->getConcernedQueryVariablesWithoutPrefix(['pfx_one' => 'valone', 'pfx_two' => 'valtwo', 'pfx_three' => 'valthree'])
        );
    }

    public function testGetConcernedQueryVariablesWithoutPrefixThrowsForMissingRequiredQueryVariables()
    {
        $this->expectException(RequiredQueryVariablesMissingException::class);

        $rewrite = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', ['one' => 'one', 'two' => 'two'], 'somehandler');

        $rewrite->getConcernedQueryVariablesWithoutPrefix(['one' => 'valone']);
    }

    public function testWithEmptyQuery()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty string');

        new Rewrite(['GET'], 'someregex', '', ['some' => 'some'], 'somehandler');
    }

    public function testWithInvalidMethods()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid methods list');

        new Rewrite(['GET', 'BADMETHOD'], 'someregex', 'some=query', ['some' => 'some'], 'somehandler');
    }

    public function testWithIsActiveCallback()
    {
        $one = new Rewrite(['GET'], 'someregex', 'index.php?var=value', ['var' => 'var'], 'somehandler');

        $two = new Rewrite(['GET'], 'anotherregex', 'index.php?var=value', ['var' => 'var'], 'anotherhandler');
        $two->setIsActiveCallback('anotherisactivecallback');

        $three = new Rewrite(['GET'], 'yetanotherregex', 'index.php?var=value', ['var' => 'var'], 'yetanotherhandler', 'yetanotherisactivecallback');

        $this->assertFalse($one->hasIsActiveCallback());
        $this->assertInstanceOf(Closure::class, $one->getIsActiveCallback());
        $this->assertTrue(($one->getIsActiveCallback())());

        $this->assertTrue($two->hasIsActiveCallback());
        $this->assertSame('anotherisactivecallback', $two->getIsActiveCallback());

        $this->assertTrue($three->hasIsActiveCallback());
        $this->assertSame('yetanotherisactivecallback', $three->getIsActiveCallback());
    }
}
