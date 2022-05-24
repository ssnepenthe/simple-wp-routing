<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use FastRoute\BadRouteException;
use PHPUnit\Framework\TestCase;
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
                [
                    '^/test$' => '',
                ]
            ],
            [
                '/test/{param}',
                [
                    '^/test/([^/]+)$' => 'index.php?param=$matches[1]',
                ],
            ],
            [
                '/te{ param }st',
                [
                    '^/te([^/]+)st$' => 'index.php?param=$matches[1]',
                ],
            ],
            [
                '/test/{param1}/test2/{param2}',
                [
                    '^/test/([^/]+)/test2/([^/]+)$' => 'index.php?param1=$matches[1]&param2=$matches[2]',
                ],
            ],
            [
                '/test/{param:\d+}',
                [
                    '^/test/(\d+)$' => 'index.php?param=$matches[1]',
                ],
            ],
            [
                '/test/{ param : \d{1,9} }',
                [
                    '^/test/(\d{1,9})$' => 'index.php?param=$matches[1]',
                ],
            ],
            [
                '/test[opt]',
                [
                    '^/test$' => '',
                    '^/testopt$' => '',
                ],
            ],
            [
                '/test[/{param}]',
                [
                    '^/test$' => '',
                    '^/test/([^/]+)$' => 'index.php?param=$matches[1]',
                ],
            ],
            [
                '/{param}[opt]',
                [
                    '^/([^/]+)$' => 'index.php?param=$matches[1]',
                    '^/([^/]+)opt$' => 'index.php?param=$matches[1]',
                ],
            ],
            [
                '/test[/{name}[/{id:[0-9]+}]]',
                [
                    '^/test$' => '',
                    '^/test/([^/]+)$' => 'index.php?name=$matches[1]',
                    '^/test/([^/]+)/([0-9]+)$' => 'index.php?name=$matches[1]&id=$matches[2]',
                ],
            ],
            [
                '/{foo-bar}',
                [
                    '^/([^/]+)$' => 'index.php?foo-bar=$matches[1]',
                ],
            ],
            [
                '/{_foo:.*}',
                [
                    '^/(.*)$' => 'index.php?_foo=$matches[1]',
                ],
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
                '[[test]]',
                'Empty optional part',
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
