<?php

namespace ToyWpRouting\Tests;

use DI\Container;
use Invoker\Invoker;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\Route;

use function DI\value;

class RouteTest extends TestCase
{
	public function testSetPrefix()
	{
		$route = new Route(['GET'], 'someroutestring', function() {});

		$this->assertSame('', $route->getPrefix());

		$route->setPrefix('pfx_');

		$this->assertSame('pfx_', $route->getPrefix());
	}

	public function testUnless()
	{
		$route = new Route(['GET'], 'someroutestring', function() {});

		$route->unless(function() { return false; });

		$this->assertTrue(($route->getIsActiveCallback())());

		$route->unless(function() { return true; });

		$this->assertFalse(($route->getIsActiveCallback())());
	}

	public function testUnlessWithInvoker()
	{
		$runCount = 0;

		$container = new Container();
		$container->set('truthy', true);
		$container->set('falsy', false);
		$container->set('callback', value(function() use (&$runCount) {
			$runCount++;
			return false;
		}));

		$invoker = new Invoker(new ParameterNameContainerResolver($container), $container);

		$route = new Route(['GET'], 'someroutestring', function() {});

		// Can resolve callable from container.
		$route->unless('callback');

		$this->assertTrue(($route->getIsActiveCallback())($invoker));
		$this->assertSame(1, $runCount);

		// Can provide container values as params to callback.
		$route->unless(function($truthy) use (&$runCount) {
			$runCount++;
			return $truthy;
		});

		$this->assertFalse(($route->getIsActiveCallback())($invoker));
		$this->assertSame(2, $runCount);

		$route->unless(function($falsy) use (&$runCount) {
			$runCount++;
			return $falsy;
		});

		$this->assertTrue(($route->getIsActiveCallback())($invoker));
		$this->assertSame(3, $runCount);
	}

	public function testWhen()
	{
		$route = new Route(['GET'], 'someroutestring', function() {});

		$route->when(function() { return true; });

		$this->assertTrue(($route->getIsActiveCallback())());

		$route->when(function() { return false; });

		$this->assertFalse(($route->getIsActiveCallback())());
	}
}
