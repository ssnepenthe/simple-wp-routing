<?php

declare(strict_types=1);

namespace ToyWpRouting;

use InvalidArgumentException;

class Route
{
    /**
     * @var mixed
     */
    protected $handler;

    /**
     * @var ?InvocationStrategyInterface
     */
    protected $invocationStrategy;

    /**
     * @var mixed
     */
    protected $isActiveCallback;

    /**
     * @var array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS">
     */
    protected array $methods;

    protected string $prefix = '';

    protected string $route;

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param mixed $handler
     */
    public function __construct(array $methods, string $route, $handler)
    {
        if (! Support::isValidMethodsList($methods)) {
            throw new InvalidArgumentException('@todo');
        }

        $this->methods = $methods;
        $this->route = $route;
        $this->handler = $handler;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    public function getInvocationStrategy(): ?InvocationStrategyInterface
    {
        return $this->invocationStrategy;
    }

    /**
     * @return mixed
     */
    public function getIsActiveCallback()
    {
        return $this->isActiveCallback;
    }

    /**
     * @return array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS">
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setInvocationStrategy(InvocationStrategyInterface $invocationStrategy): self
    {
        $this->invocationStrategy = $invocationStrategy;

        return $this;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param mixed $when
     */
    public function when($when): self
    {
        $this->isActiveCallback = $when;

        return $this;
    }
}
