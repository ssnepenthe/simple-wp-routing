<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\Router;

class RouterTest extends TestCase
{
    public function testCreate()
    {
        $router = new Router();

        // Indirectly via get.
        $router->get('one/{two}', 'onehandler');

        // Also groups + autoslash.
        $router->group('three', function ($router) {
            $router->get('{four}', 'threehandler');
        });

        [$rewriteOne, $rewriteTwo] = $router->rewriteCollection()->getRewrites();

        $this->assertSame('^(?|one/([^/]+))$', $rewriteOne->getRegex());
        $this->assertSame('index.php?two=$matches[1]&__routeType=variable', $rewriteOne->getQuery());

        $this->assertSame('^(?|three/([^/]+))$', $rewriteTwo->getRegex());
        $this->assertSame('index.php?four=$matches[1]&__routeType=variable', $rewriteTwo->getQuery());
    }

    public function testCreateWithPrefix()
    {
        $router = new Router();
        $router->setPrefix('pfx_');

        // Indirectly via get.
        $router->get('one/{two}', 'onehandler');

        // Also groups + autoslash.
        $router->group('three', function ($router) {
            $router->get('{four}', 'threehandler');
        });

        [$rewriteOne, $rewriteTwo] = $router->rewriteCollection()->getRewrites();

        $this->assertSame('^(?|one/([^/]+))$', $rewriteOne->getRegex());
        $this->assertSame('index.php?pfx_two=$matches[1]&pfx___routeType=variable', $rewriteOne->getQuery());

        $this->assertSame('^(?|three/([^/]+))$', $rewriteTwo->getRegex());
        $this->assertSame('index.php?pfx_four=$matches[1]&pfx___routeType=variable', $rewriteTwo->getQuery());
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

        $methods = array_map(fn ($rewrite) => $rewrite->getMethods(), $router->rewriteCollection()->getRewrites());

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

        $regexes = array_map(fn ($rewrite) => $rewrite->getRegex(), $router->rewriteCollection()->getRewrites());

        $this->assertSame('^(?|one/two)$', $regexes[0]);
        $this->assertSame('^(?|one/three)$', $regexes[1]);
        $this->assertSame('^(?|one/four/five)$', $regexes[2]);
    }
}
