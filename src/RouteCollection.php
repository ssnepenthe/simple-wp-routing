<?php

namespace ToyWpRouting;

use RuntimeException;

class RouteCollection
{
    protected $locked = false;
    protected $prefix;
    protected $routes = [];

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    public function add(array $methods, string $route, $handler)
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add routes when toue collection is locked');
        }

        $route = new Route($methods, $route, $handler);

        if ('' !== $this->prefix) {
            $route->setPrefix($this->prefix);
        }

        $this->routes[] = $route;

        return $route;
    }

    public function any(string $route, $handler)
    {
        return $this->add(
            ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            $route,
            $handler
        );
    }

    public function delete(string $route, $handler)
    {
        return $this->add(['DELETE'], $route, $handler);
    }

    public function get(string $route, $handler)
    {
        return $this->add(['GET', 'HEAD'], $route, $handler);
    }

    public function lock()
    {
        $this->locked = true;

        return $this;
    }

    public function options(string $route, $handler)
    {
        return $this->add(['OPTIONS'], $route, $handler);
    }

    public function patch(string $route, $handler)
    {
        return $this->add(['PATCH'], $route, $handler);
    }

    public function post(string $route, $handler)
    {
        return $this->add(['POST'], $route, $handler);
    }

    public function put(string $route, $handler)
    {
        return $this->add(['PUT'], $route, $handler);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }
}
