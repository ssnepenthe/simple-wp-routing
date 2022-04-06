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
                    '^/test$' => ['matchedRoute' => 'cdfa5ee1cd1e0eafb3f30cea96b18288'],
                ]
            ],
            [
                '/test/{param}',
                [
                    '^/test/([^/]+)$' => [
                        'param' => '$matches[1]',
                        'matchedRoute' => 'a5bb02fcd399fe91cb6b0112934af815',
                    ],
                ],
            ],
            [
                '/te{ param }st',
                [
                    '^/te([^/]+)st$' => [
                        'param' => '$matches[1]',
                        'matchedRoute' => '8f049396c0c90f7e08928e7582710c9f'
                    ],
                ],
            ],
            [
                '/test/{param1}/test2/{param2}',
                [
                    '^/test/([^/]+)/test2/([^/]+)$' => [
                        'param1' => '$matches[1]',
                        'param2' => '$matches[2]',
                        'matchedRoute' => '4cf88822f35dc70a75a96868c196f8f5',
                    ],
                ],
            ],
            [
                '/test/{param:\d+}',
                [
                    '^/test/(\d+)$' => [
                        'param' => '$matches[1]',
                        'matchedRoute' => 'afc5c61e09c13937f2932256ddc7c193',
                    ],
                ],
            ],
            [
                '/test/{ param : \d{1,9} }',
                [
                    '^/test/(\d{1,9})$' => [
                        'param' => '$matches[1]',
                        'matchedRoute' => 'e90fa3f51494c24a65475678ac8c2702',
                    ],
                ],
            ],
            [
                '/test[opt]',
                [
                    '^/test$' => ['matchedRoute' => 'cdfa5ee1cd1e0eafb3f30cea96b18288'],
                    '^/testopt$' => ['matchedRoute' => '81f44215d983ccabef2149c0e183132e'],
                ],
            ],
            [
                '/test[/{param}]',
                [
                    '^/test$' => ['matchedRoute' => 'cdfa5ee1cd1e0eafb3f30cea96b18288'],
                    '^/test/([^/]+)$' => [
                        'param' => '$matches[1]',
                        'matchedRoute' => 'a5bb02fcd399fe91cb6b0112934af815'
                    ],
                ],
            ],
            [
                '/{param}[opt]',
                [
                    '^/([^/]+)$' => [
                        'param' => '$matches[1]',
                        'matchedRoute' => '6523a3a9e0048579075fdb6c4dd7e010',
                    ],
                    '^/([^/]+)opt$' => [
                        'param' => '$matches[1]',
                        'matchedRoute' => 'f28b33f70e7f876d5f4239b2c92a8047',
                    ],
                ],
            ],
            [
                '/test[/{name}[/{id:[0-9]+}]]',
                [
                    '^/test$' => ['matchedRoute' => 'cdfa5ee1cd1e0eafb3f30cea96b18288'],
                    '^/test/([^/]+)$' => [
                        'name' => '$matches[1]',
                        'matchedRoute' => 'a5bb02fcd399fe91cb6b0112934af815'
                    ],
                    '^/test/([^/]+)/([0-9]+)$' => [
                        'name' => '$matches[1]',
                        'id' => '$matches[2]',
                        'matchedRoute' => '49873b1373ae276c10761836cbdcd967',
                    ],
                ],
            ],
            [
                '/{foo-bar}',
                [
                    '^/([^/]+)$' => [
                        'foo-bar' => '$matches[1]',
                        'matchedRoute' => '6523a3a9e0048579075fdb6c4dd7e010',
                    ],
                ],
            ],
            [
                '/{_foo:.*}',
                [
                    '^/(.*)$' => [
                        '_foo' => '$matches[1]',
                        'matchedRoute' => 'c2f74f61748374d7decb9747fffde00e',
                    ],
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
