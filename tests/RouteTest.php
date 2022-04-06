<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\Route;

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
