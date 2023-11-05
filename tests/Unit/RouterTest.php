<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\Router;

class RouterTest extends TestCase
{
    public function testCreate()
    {
        // @todo
        // Test with group
        // Test with prefix
        // Test first rewrite rule gets requried qvs
        // Test with invocation strat


        // Indirectly via get.
        $router = new Router();
        $router->get('one/{two}[/{three}[/four]]', 'handler');

        $rules = $this->getRewrites($router)[0]->getRules();

        $this->assertCount(3, $rules);

        $this->assertSame('^one/([^/]+)$', $rules[0]->getRegex());
        $this->assertSame('index.php?two=$matches[1]', $rules[0]->getQuery());

        $this->assertSame('^one/([^/]+)/([^/]+)$', $rules[1]->getRegex());
        $this->assertSame('index.php?two=$matches[1]&three=$matches[2]', $rules[1]->getQuery());

        $this->assertSame('^one/([^/]+)/([^/]+)/four$', $rules[2]->getRegex());
        $this->assertSame('index.php?two=$matches[1]&three=$matches[2]', $rules[2]->getQuery());
    }

    public function testHttpMethodShorthandMethods()
    {
        $router = new Router();
        $router->any('anyroute', 'handler');
        $router->delete('deleteroute', 'handler');
        $router->get('getroute', 'handler');
        $router->options('optionsroute', 'handler');
        $router->patch('patchroute', 'handler');
        $router->post('postroute', 'handler');
        $router->put('putroute', 'handler');

        $methods = array_map(fn ($rewrite) => $rewrite->getMethods(), $this->getRewrites($router));

        $this->assertSame(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $methods[0]);
        $this->assertSame(['DELETE'], $methods[1]);
        $this->assertSame(['GET', 'HEAD'], $methods[2]);
        $this->assertSame(['OPTIONS'], $methods[3]);
        $this->assertSame(['PATCH'], $methods[4]);
        $this->assertSame(['POST'], $methods[5]);
        $this->assertSame(['PUT'], $methods[6]);
    }

    public function testGroup()
    {
        $router = new Router();
        $router->group('one', function ($router) {
            $router->get('two', 'handler');
            $router->get('three', 'handler');

            $router->group('four', function ($router) {
                $router->get('five', 'handler');
            });
        });

        $regexes = array_map(fn ($rewrite) => $rewrite->getRules()[0]->getRegex(), $this->getRewrites($router));

        $this->assertSame('^one/two$', $regexes[0]);
        $this->assertSame('^one/three$', $regexes[1]);
        $this->assertSame('^one/four/five$', $regexes[2]);
    }

    private function getRewrites(Router $router): array
    {
        return iterator_to_array($router->rewriteCollection()->getRewrites());
    }
}
