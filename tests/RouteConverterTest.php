<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteInterface;
use ToyWpRouting\Route;
use ToyWpRouting\RouteCollection;
use ToyWpRouting\RouteConverter;

class RouteConverterTest extends TestCase
{
    public function testConvert()
    {
        $route = new Route(['GET'], 'someroutestring', 'somehandler');

        $rewrite = (new RouteConverter())->convert($route);

        // Redundant due to typing in RewriteCollection...
        $this->assertInstanceOf(RewriteInterface::class, $rewrite);

        $this->assertSame(['GET'], $rewrite->getMethods());
        $this->assertSame('somehandler', $rewrite->getHandler());
    }

    public function testConvertCollection()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->get('one', 'somehandler');
        $routeCollection->post('two[three]', 'somehandler');

        $convertedCollection = (new RouteConverter())->convertCollection($routeCollection);

        $this->assertInstanceOf(RewriteCollection::class, $convertedCollection);

        $this->assertCount(2, $convertedCollection->getRewrites());
        $this->assertSame(
            ['^one$', '^two$', '^twothree$'],
            array_keys($convertedCollection->getRewriteRules())
        );
    }

    public function testConvertCollectionWithPrefix()
    {
        $routeCollection = new RouteCollection('pfx_');
        $routeCollection->get('one', 'somehandler');

        $convertedCollection = (new RouteConverter())->convertCollection($routeCollection);

        $this->assertSame('pfx_', $convertedCollection->getPrefix());
    }

    public function testConvertWithIsActiveCallback()
    {
        // isActiveCallback is automatically applied to rewrites.
        $route = new Route(['GET'], 'someroutestring', 'somehandler');
        $route->when(function () {
            return false;
        });

        $rewrite = (new RouteConverter())->convert($route);

        $this->assertFalse($rewrite->getIsActiveCallback()());
    }

    public function testConvertWithMultipleMethods()
    {
        // Multiple request methods result in multiple rewrites.
        $route = new Route(['GET', 'POST'], 'someroutestring', 'somehandler');

        $rewrite = (new RouteConverter())->convert($route);

        $this->assertSame(['GET', 'POST'], $rewrite->getMethods());
    }

    public function testConvertWithOptionalRouteSegments()
    {
        // Optional route segments result in multiple rewrites.
        $route = new Route(['GET'], 'someroute[string]', 'somehandler');

        $rewrite = (new RouteConverter())->convert($route);

        $this->assertSame('^someroute$', $rewrite->getRules()[0]->getRegex());
        $this->assertSame('^someroutestring$', $rewrite->getRules()[1]->getRegex());
    }

    public function testConvertWithPrefix()
    {
        // Route prefixes are automatically applied to rewrites.
        $route = new Route(['GET'], 'someroute{string}', 'somehandler');
        $route->setPrefix('pfx_');

        $rewrite = (new RouteConverter())->convert($route);

        $this->assertSame(
            ['pfx_string', 'pfx_matchedRule'],
            array_keys($rewrite->getRules()[0]->getQueryVariables())
        );
    }
}
