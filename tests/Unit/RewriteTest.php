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
        $this->assertSame(['var'], $rewrite->getRequiredQueryVariables());
        $this->assertSame('index.php?var=value', $rewrite->getQuery());
        $this->assertSame(['var' => 'var'], $rewrite->getQueryVariables());
    }

    public function testMapQueryVariable()
    {
        $rewriteOne = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', 'somehandler');
        $rewriteTwo = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', 'somehandler', 'pfx_');

        $this->assertSame('one', $rewriteOne->mapQueryVariable('one'));
        $this->assertSame('two', $rewriteOne->mapQueryVariable('two'));
        $this->assertNull($rewriteOne->mapQueryVariable('three'));

        $this->assertSame('one', $rewriteTwo->mapQueryVariable('pfx_one'));
        $this->assertSame('two', $rewriteTwo->mapQueryVariable('pfx_two'));
        $this->assertNull($rewriteTwo->mapQueryVariable('pfx_three'));

        $this->assertNull($rewriteTwo->mapQueryVariable('two'));
    }

    public function testValidate()
    {
        $rewrite = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', 'somehandler');

        $this->assertSame(
            ['one' => 'valone', 'two' => 'valtwo'],
            $rewrite->validate(['one' => 'valone', 'two' => 'valtwo'])
        );

        // Only checking for required vars - extras are untouched.
        $this->assertSame(
            ['one' => 'valone', 'two' => 'valtwo', 'three' => 'valthree'],
            $rewrite->validate(['one' => 'valone', 'two' => 'valtwo', 'three' => 'valthree'])
        );
    }

    public function testValidateThrowsForMissingRequiredQueryVariables()
    {
        $this->expectException(RequiredQueryVariablesMissingException::class);

        $rewrite = new Rewrite(['GET'], 'regex', 'one=valone&two=valtwo', 'somehandler');

        $rewrite->validate(['one' => 'valone']);
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
