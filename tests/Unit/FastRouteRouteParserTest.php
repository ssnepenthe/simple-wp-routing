<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\Exception\BadRouteException;
use ToyWpRouting\FastRouteRouteParser;

/**
 * Adapted from FastRoute tests.
 */
class FastRouteRouteParserTest extends TestCase
{
    public function provideTestParse()
    {
        return [
            [
                '/test',
                ['^(?|/test)$', ['__routeType' => 'static']],
            ],
            [
                '/test/{param}',
                ['^(?|/test/([^/]+))$', ['param' => '$matches[1]', '__routeType' => 'variable']],
            ],
            [
                '/te{ param }st',
                ['^(?|/te([^/]+)st)$', ['param' => '$matches[1]', '__routeType' => 'variable']],
            ],
            [
                '/test/{param1}/test2/{param2}',
                ['^(?|/test/([^/]+)/test2/([^/]+))$', ['param1' => '$matches[1]', 'param2' => '$matches[2]', '__routeType' => 'variable']],
            ],
            [
                '/test/{param:\d+}',
                ['^(?|/test/(\d+))$', ['param' => '$matches[1]', '__routeType' => 'variable']],
            ],
            [
                '/test/{ param : \d{1,9} }',
                ['^(?|/test/(\d{1,9}))$', ['param' => '$matches[1]', '__routeType' => 'variable']],
            ],
            [
                '/test[opt]',
                ['^(?|/test|/testopt)$', ['__routeType' => 'static']],
            ],
            [
                '/test[/{param}]',
                ['^(?|/test|/test/([^/]+))$', ['param' => '$matches[1]', '__routeType' => 'variable']],
            ],
            [
                '/{param}[opt]',
                ['^(?|/([^/]+)|/([^/]+)opt)$', ['param' => '$matches[1]', '__routeType' => 'variable']],
            ],
            [
                '/test[/{name}[/{id:[0-9]+}]]',
                ['^(?|/test|/test/([^/]+)|/test/([^/]+)/([0-9]+))$', ['name' => '$matches[1]', 'id' => '$matches[2]', '__routeType' => 'variable']],
            ],
            [
                '/{foo-bar}',
                ['^(?|/([^/]+))$', ['foo-bar' => '$matches[1]', '__routeType' => 'variable']],
            ],
            [
                '/{_foo:.*}',
                ['^(?|/(.*))$', ['_foo' => '$matches[1]', '__routeType' => 'variable']],
            ],
        ];
    }

    public function provideTestParseError()
    {
        return [
            [
                '',
                'Empty routes not allowed',
            ],
            [
                '[test]',
                'Empty routes not allowed',
            ],
            [
                '/test[opt',
                "Number of opening '[' and closing ']' does not match",
            ],
            [
                '/test[opt[opt2]',
                "Number of opening '[' and closing ']' does not match",
            ],
            [
                '/testopt]',
                "Number of opening '[' and closing ']' does not match",
            ],
            [
                '/test[]',
                'Empty optional part',
            ],
            [
                '/test[[opt]]',
                'Empty optional part',
            ],
            [
                // @todo This is a weird one after inlining parser...
                // FastRoute parser parsePlaceholders method returns ['', '', 'test'].
                // First empty string makes it past the fastroute exception due to $n === 0 and lands on our exception.
                // Looking through fastroute history it still isn't clear why that n !== 0 check is performed.
                // May need to revisit.
                '[[test]]',
                // 'Empty optional part',
                'Empty routes not allowed'
            ],
            [
                '/test[/opt]/required',
                'Optional segments can only occur at the end of a route',
            ],
        ];
    }

    /** @dataProvider provideTestParse */
    public function testParse($input, $output)
    {
        $parser = new FastRouteRouteParser();

        $this->assertSame($output, $parser->parse($input));
    }

    /** @dataProvider provideTestParseError */
    public function testParseError($input, $exceptionMessage)
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $parser = new FastRouteRouteParser();
        $parser->parse($input);
    }
}
