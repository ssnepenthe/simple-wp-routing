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
        $route = new Route(['GET'], 'someroutestring', function () {
        });

        $this->assertSame('', $route->getPrefix());

        $route->setPrefix('pfx_');

        $this->assertSame('pfx_', $route->getPrefix());
    }

    public function testWhen()
    {
        $route = new Route(['GET'], 'someroutestring', function () {
        });

        $route->when(function () {
            return true;
        });

        $this->assertTrue(($route->getIsActiveCallback())());

        $route->when(function () {
            return false;
        });

        $this->assertFalse(($route->getIsActiveCallback())());
    }
}
