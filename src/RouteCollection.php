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

    public function add(Route $route): Route
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add routes when route collection is locked');
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
            $this->create(
                ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                $route,
                $handler
            )
        );
    }

    /**
     * @param mixed $handler
     */
    public function delete(string $route, $handler): Route
    {
        return $this->add(
            $this->create(['DELETE'], $route, $handler)
        );
    }

    /**
     * @param mixed $handler
     */
    public function get(string $route, $handler): Route
    {
        return $this->add(
            $this->create(['GET', 'HEAD'], $route, $handler)
        );
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
        return $this->add(
            $this->create(['OPTIONS'], $route, $handler)
        );
    }

    /**
     * @param mixed $handler
     */
    public function patch(string $route, $handler): Route
    {
        return $this->add(
            $this->create(['PATCH'], $route, $handler)
        );
    }

    /**
     * @param mixed $handler
     */
    public function post(string $route, $handler): Route
    {
        return $this->add(
            $this->create(['POST'], $route, $handler)
        );
    }

    /**
     * @param mixed $handler
     */
    public function put(string $route, $handler): Route
    {
        return $this->add(
            $this->create(['PUT'], $route, $handler)
        );
    }

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param mixed $handler
     */
    protected function create(array $methods, string $route, $handler): Route
    {
        $route = new Route($methods, $route, $handler);

        if ('' !== $this->prefix) {
            $route->setPrefix($this->prefix);
        }

        $route->setInvocationStrategy($this->invocationStrategy);

        return $route;
    }
}
