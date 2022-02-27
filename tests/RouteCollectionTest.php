<?php

namespace ToyWpRouting\Tests;

use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Route;
use ToyWpRouting\RouteCollection;

class RouteCollectionTest extends TestCase
{
    public function testAdd()
    {
        $collection = new RouteCollection();
        $collection->add(['GET'], 'someroutestring', function () {
        });

        $route = $collection->getRoutes()[0];

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame('someroutestring', $route->getRoute());
        $this->assertInstanceOf(Closure::class, $route->getHandler());
        $this->assertSame('', $route->getPrefix());
    }

    public function testAddWhenLocked()
    {
        $this->expectException(RuntimeException::class);

        $collection = new RouteCollection();
        $collection->lock();
        $collection->add(['GET'], 'someroutestring', function () {
        });
    }

    public function testAddWithPrefix()
    {
        $collection = new RouteCollection('pfx_');
        $collection->add(['GET'], 'someroutestring', function () {
        });

        $this->assertSame('pfx_', $collection->getRoutes()[0]->getPrefix());
    }

    public function testShorthandMethods()
    {
        $collection = new RouteCollection();
        $collection->any('someroutestring', function () {
        });
        $this->assertSame(
            ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            $collection->getRoutes()[0]->getMethods()
        );

        $collection = new RouteCollection();
        $collection->delete('someroutestring', function () {
        });
        $this->assertSame(['DELETE'], $collection->getRoutes()[0]->getMethods());

        $collection = new RouteCollection();
        $collection->get('someroutestring', function () {
        });
        $this->assertSame(['GET', 'HEAD'], $collection->getRoutes()[0]->getMethods());

        $collection = new RouteCollection();
        $collection->options('someroutestring', function () {
        });
        $this->assertSame(['OPTIONS'], $collection->getRoutes()[0]->getMethods());

        $collection = new RouteCollection();
        $collection->patch('someroutestring', function () {
        });
        $this->assertSame(['PATCH'], $collection->getRoutes()[0]->getMethods());

        $collection = new RouteCollection();
        $collection->post('someroutestring', function () {
        });
        $this->assertSame(['POST'], $collection->getRoutes()[0]->getMethods());

        $collection = new RouteCollection();
        $collection->put('someroutestring', function () {
        });
        $this->assertSame(['PUT'], $collection->getRoutes()[0]->getMethods());
    }

    public function testIsLocked()
    {
        $collection = new RouteCollection();

        $this->assertFalse($collection->isLocked());

        $collection->lock();

        $this->assertTrue($collection->isLocked());
    }
}
