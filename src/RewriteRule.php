<?php

declare(strict_types=1);

namespace ToyWpRouting;

// @todo More getters? prefixedToUnprefixedVariableMap, queryVariables, prefix, queryArray, prefixedQueryArray, individual values from query array?
class RewriteRule implements RewriteRuleInterface
{
    protected $prefix = '';
    protected $prefixedQueryArray;
    protected $query;
    protected $queryArray;
    protected $rawQuery;
    protected $regex;

    public function __construct(string $regex, string $query, string $prefix = '')
    {
        $this->regex = $regex;
        $this->rawQuery = $query;
        $this->prefix = $prefix;

        // @todo Lazily parse query for cached rewrite collection sake?
        $this->parseQuery();
    }

    public function getHash(): string
    {
        return md5($this->regex);
    }

    public function getPrefixedQueryArray(): array
    {
        return $this->prefixedQueryArray;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getQueryArray(): array
    {
        return $this->queryArray;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    protected function parseQuery(): void
    {
        $queryArray = '' === $this->rawQuery ? [] : Support::parseQuery($this->rawQuery);

        $queryArray['matchedRule'] = $this->getHash();

        $prefixedQueryArray = Support::applyPrefixToKeys($queryArray, $this->prefix);

        $query = Support::buildQuery($prefixedQueryArray);

        $this->prefixedQueryArray = $prefixedQueryArray;
        $this->query = $query;
        $this->queryArray = $queryArray;
    }
}
