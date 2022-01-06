<?php

namespace ToyWpRouting;

class OptimizedRewrite extends AbstractRewrite
{
    protected $handler;
    protected $method;
    protected $prefixedToUnprefixedQueryVariablesMap;
    protected $query;
    protected $queryVariables;
    protected $regex;

    public function __construct(
        string $method,
        string $regex,
        $handler,
        array $prefixedToUnprefixedQueryVariablesMap,
        string $query,
        array $queryVariables
    ) {
        $this->method = $method;
        $this->regex = $regex;
        $this->handler = $handler;
        $this->prefixedToUnprefixedQueryVariablesMap = $prefixedToUnprefixedQueryVariablesMap;
        $this->query = $query;
        $this->queryVariables = $queryVariables;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPrefixedToUnprefixedQueryVariablesMap(): array
    {
        return $this->prefixedToUnprefixedQueryVariablesMap;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getQueryVariables(): array
    {
        return $this->queryVariables;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }
}
