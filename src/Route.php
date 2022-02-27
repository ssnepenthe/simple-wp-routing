<?php

namespace ToyWpRouting;

class Route
{
    protected $handler;
    protected $isActiveCallback;
    protected $methods;
    protected $prefix = '';
    protected $route;

    public function __construct(array $methods, string $route, $handler)
    {
        $this->methods = $methods;
        $this->route = $route;
        $this->handler = $handler;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getIsActiveCallback()
    {
        return $this->isActiveCallback;
    }

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

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function when($when)
    {
        $this->isActiveCallback = $when;

        return $this;
    }
}
