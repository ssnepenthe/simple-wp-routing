<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use SimpleWpRouting\Support\Rewrite;
use SimpleWpRouting\Support\Route;

class RouteTest extends TestCase
{
    public function testSetIsActiveCallback()
    {
        $activeCallback = 'irrelevantIsActiveCallback';
        $rewrites = [
            new Rewrite(['GET'], 'one', 'one', ['one' => 'one'], 'one'),
            new Rewrite(['POST'], 'two', 'two', ['two' => 'two'], 'two'),
            new Rewrite(['PUT'], 'three', 'three', ['three' => 'three'], 'three'),
        ];
        $route = new Route(...$rewrites);

        // Method should just be a proxy to all underlying rewrites.
        $route->setIsActiveCallback($activeCallback);

        $this->assertSame($activeCallback, $rewrites[0]->getIsActiveCallback());
        $this->assertSame($activeCallback, $rewrites[1]->getIsActiveCallback());
        $this->assertSame($activeCallback, $rewrites[2]->getIsActiveCallback());
    }
}
