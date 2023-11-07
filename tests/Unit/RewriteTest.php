<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\Exception\RequiredQueryVariablesMissingException;
use ToyWpRouting\Rewrite;

class RewriteTest extends TestCase
{
    public function testGetters()
    {
        $rewrite = new Rewrite(['GET'], 'someregex', 'index.php?var=value', 'somehandler');

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertNull($rewrite->getIsActiveCallback());
        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame('someregex', $rewrite->getRegex());
        $this->assertSame('index.php?var=value', $rewrite->getQuery());
        $this->assertSame(['var' => 'var'], $rewrite->getQueryVariables());
    }

    public function testGetConcernedQueryVariablesWithoutPrefix()
    {
        // Without prefix.
        $rewrite = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', 'somehandler');

        $this->assertSame(
            ['one' => 'valone', 'two' => 'valtwo'],
            $rewrite->getConcernedQueryVariablesWithoutPrefix(['one' => 'valone', 'two' => 'valtwo', 'three' => 'valthree'])
        );

        // With prefix.
        $rewrite = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', 'somehandler', 'pfx_');

        $this->assertSame(
            ['one' => 'valone', 'two' => 'valtwo'],
            $rewrite->getConcernedQueryVariablesWithoutPrefix(['pfx_one' => 'valone', 'pfx_two' => 'valtwo', 'pfx_three' => 'valthree'])
        );
    }

    public function testGetConcernedQueryVariablesWithoutPrefixThrowsForMissingRequiredQueryVariables()
    {
        $this->expectException(RequiredQueryVariablesMissingException::class);

        $rewrite = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', 'somehandler');

        $rewrite->getConcernedQueryVariablesWithoutPrefix(['one' => 'valone']);
    }

    public function testWithInvalidMethods()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid methods list');

        new Rewrite(['GET', 'BADMETHOD'], 'someregex', 'some=query', 'somehandler');
    }

    public function testWithIsActiveCallback()
    {
        $one = new Rewrite(['GET'], 'someregex', 'index.php?var=value', 'somehandler');
        $one->setIsActiveCallback('someisactivecallback');

        $two = new Rewrite(['GET'], 'anotherregex', 'index.php?var=value', 'anotherhandler', '', 'anotherisactivecallback');

        $this->assertSame('someisactivecallback', $one->getIsActiveCallback());
        $this->assertSame('anotherisactivecallback', $two->getIsActiveCallback());
    }
}
