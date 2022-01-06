<?php

namespace ToyWpRouting;

class Rewrite extends AbstractRewrite
{
    protected $method;
    protected $regex;
    protected $queryArray;
    protected $handler;
    protected $prefix;

    protected $prefixedQueryArray;
    protected $queryVariables;
    protected $prefixedToUnprefixedQueryVariablesMap;
    protected $query;

    public function __construct(
        string $method,
        string $regex,
        array $queryArray,
        $handler,
        string $prefix = ''
    ) {
        $this->method = $method;
        $this->regex = $regex;
        $this->queryArray = $queryArray;
        $this->handler = $handler;
        $this->prefix = $prefix;
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
        if (! is_array($this->prefixedToUnprefixedQueryVariablesMap)) {
            $this->prefixedToUnprefixedQueryVariablesMap = array_combine(
                array_keys($this->getPrefixedQueryArray()),
                array_keys($this->queryArray)
            );
        }

        return $this->prefixedToUnprefixedQueryVariablesMap;
    }

    public function getQuery(): string
    {
        if (! is_string($this->query)) {
            $this->query = $this->buildQuery($this->getPrefixedQueryArray());
        }

        return $this->query;
    }

    public function getQueryVariables(): array
    {
        if (! is_array($this->queryVariables)) {
            $this->queryVariables = array_keys($this->getPrefixedQueryArray());
        }

        return $this->queryVariables;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    protected function applyPrefix(string $value): string
    {
        // @todo verify $value doesn't already start with $this->prefix?
        return "{$this->prefix}{$value}";
    }

    protected function buildQuery(array $queryArray): string
    {
        return 'index.php?' . implode('&', array_map(function ($key, $value) {
            return "{$key}={$value}";
        }, array_keys($queryArray), $queryArray));
    }

    protected function getPrefixedQueryArray(): array
    {
        if (! is_array($this->prefixedQueryArray)) {
            $this->prefixedQueryArray = [];

            foreach ($this->queryArray as $key => $value) {
                $this->prefixedQueryArray[$this->applyPrefix($key)] = $value;
            }
        }

        return $this->prefixedQueryArray;
    }
}
