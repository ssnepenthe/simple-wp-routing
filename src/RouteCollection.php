<?php

declare(strict_types=1);

namespace ToyWpRouting;

use RuntimeException;

class RouteCollection
{
    protected InvocationStrategyInterface $invocationStrategy;

    protected bool $locked = false;

    protected string $prefix;

    /**
     * @var Route[]
     */
    protected array $routes = [];

    public function __construct(
        string $prefix = '',
        ?InvocationStrategyInterface $invocationStrategy = null
    ) {
        $this->prefix = $prefix;
        $this->invocationStrategy = $invocationStrategy ?: new DefaultInvocationStrategy();
    }

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param mixed $handler
     */
    public function add(array $methods, string $route, $handler): Route
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add routes when route collection is locked');
        }

        $route = new Route($methods, $route, $handler);

        if ('' !== $this->prefix) {
            $route->setPrefix($this->prefix);
        }

        $this->routes[] = $route;

        return $route;
    }

    /**
     * @param mixed $handler
     */
    public function any(string $route, $handler): Route
    {
        return $this->add(
            ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            $route,
            $handler
        );
    }

    /**
     * @param mixed $handler
     */
    public function delete(string $route, $handler): Route
    {
        return $this->add(['DELETE'], $route, $handler);
    }

    /**
     * @param mixed $handler
     */
    public function get(string $route, $handler): Route
    {
        return $this->add(['GET', 'HEAD'], $route, $handler);
    }

    public function getInvocationStrategy(): InvocationStrategyInterface
    {
        return $this->invocationStrategy;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    /**
     * @param mixed $handler
     */
    public function options(string $route, $handler): Route
    {
        return $this->add(['OPTIONS'], $route, $handler);
    }

    /**
     * @param mixed $handler
     */
    public function patch(string $route, $handler): Route
    {
        return $this->add(['PATCH'], $route, $handler);
    }

    /**
     * @param mixed $handler
     */
    public function post(string $route, $handler): Route
    {
        return $this->add(['POST'], $route, $handler);
    }

    /**
     * @param mixed $handler
     */
    public function put(string $route, $handler): Route
    {
        return $this->add(['PUT'], $route, $handler);
    }
}
