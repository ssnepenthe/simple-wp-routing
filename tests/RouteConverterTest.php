<?php

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

		$convertedCollection = (new RouteConverter())->convert($route);

		$this->assertInstanceOf(RewriteCollection::class, $convertedCollection);

		$convertedRewrite = $convertedCollection->getRewrites()[0];

		// Redundant due to typing in RewriteCollection...
		$this->assertInstanceOf(RewriteInterface::class, $convertedRewrite);

		$this->assertSame('somehandler', $convertedRewrite->getHandler());
		$this->assertSame('GET', $convertedRewrite->getMethod());
		$this->assertSame(
			['matchedRoute' => 'matchedRoute'],
			$convertedRewrite->getPrefixedToUnprefixedQueryVariablesMap()
		);
		$this->assertStringStartsWith('index.php?matchedRoute=', $convertedRewrite->getQuery());
		$this->assertSame(['matchedRoute'], $convertedRewrite->getQueryVariables());
		$this->assertSame('^someroutestring$', $convertedRewrite->getRegex());
	}

	public function testConvertWithOptionalRouteSegments()
	{
		// Optional route segments result in multiple rewrites.
		$route = new Route(['GET'], 'someroute[string]', 'somehandler');

		$convertedCollection = (new RouteConverter())->convert($route);
		$convertedRewrites = $convertedCollection->getRewrites();

		$this->assertCount(2, $convertedRewrites);
		$this->assertSame('^someroute$', $convertedRewrites[0]->getRegex());
		$this->assertSame('^someroutestring$', $convertedRewrites[1]->getRegex());
	}

	public function testConvertWithMultipleMethods()
	{
		// Multiple request methods result in multiple rewrites.
		$route = new Route(['GET', 'POST'], 'someroutestring', 'somehandler');

		$convertedCollection = (new RouteConverter())->convert($route);
		$convertedRewrites = $convertedCollection->getRewrites();

		$this->assertCount(2, $convertedRewrites);
		$this->assertSame('GET', $convertedRewrites[0]->getMethod());
		$this->assertSame('POST', $convertedRewrites[1]->getMethod());
	}

	public function testConvertWithPrefix()
	{
		// Route prefixes are automatically applied to rewrites.
		$route = new Route(['GET'], 'someroute{string}', 'somehandler');
		$route->setPrefix('pfx_');

		$convertedCollection = (new RouteConverter())->convert($route);

		$this->assertSame(
			['pfx_string', 'pfx_matchedRoute'],
			$convertedCollection->getQueryVariables()
		);
	}

	public function testConvertWithIsActiveCallback()
	{
		// isActiveCallback is automatically applied to rewrites.
		$route = new Route(['GET'], 'someroutestring', 'somehandler');
		$route->when(function() { return false; });

		$convertedCollection = (new RouteConverter())->convert($route);
		$convertedRewrite = $convertedCollection->getRewrites()[0];

		$this->assertFalse($convertedRewrite->isActive());
	}

	public function testConvertMany()
	{
		$routeCollection = new RouteCollection();
		$routeCollection->add(['GET'], 'one', 'somehandler');
		$routeCollection->post('two[three]', 'somehandler');

		$convertedCollection = (new RouteConverter())->convertMany($routeCollection);

		$convertedRewrites = $convertedCollection->getRewrites();

		$this->assertCount(3, $convertedRewrites);
		$this->assertSame(['^one$', '^two$', '^twothree$'], array_map(function($rewrite) {
			return $rewrite->getRegex();
		}, $convertedRewrites));
	}
}
