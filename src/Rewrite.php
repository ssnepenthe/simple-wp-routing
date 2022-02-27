<?php

namespace ToyWpRouting;

class Rewrite implements RewriteInterface
{
    protected $handler;
    protected $isActiveCallback;
    protected $methods;
    protected $prefix;
    protected $regexToQueryArrayMap;

    protected $prefixedToUnprefixedQueryVariablesMap = [];
    protected $queryVariables;
    protected $rules = [];

    public function __construct(
        array $methods,
        array $regexToQueryArrayMap,
        $handler,
        string $prefix = '',
        $isActiveCallback = null
    ) {
        $this->methods = array_map('strtoupper', $methods);
        $this->regexQueryArrayPairs = $regexToQueryArrayMap;
        $this->handler = $handler;
        $this->prefix = $prefix;
        $this->isActiveCallback = $isActiveCallback;

        foreach ($regexToQueryArrayMap as $regex => $queryArray) {
            $prefixedQueryArray = [];

            foreach ($queryArray as $variable => $value) {
                $prefixedVariable = $this->applyPrefix($variable);

                $prefixedQueryArray[$prefixedVariable] = $value;

                $this->prefixedToUnprefixedQueryVariablesMap[$prefixedVariable] = $variable;
            }

            $this->rules[$regex] = $this->buildQuery($prefixedQueryArray);
        }

        $this->queryVariables = array_keys($this->prefixedToUnprefixedQueryVariablesMap);
    }

    public function getIsActiveCallback()
    {
        return $this->isActiveCallback;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getQueryVariables(): array
    {
        return $this->queryVariables;
    }

    public function getPrefixedToUnprefixedQueryVariablesMap(): array
    {
        return $this->prefixedToUnprefixedQueryVariablesMap;
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
}
