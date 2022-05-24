<?php

declare(strict_types=1);

namespace ToyWpRouting;

// @todo Tests?
class OptimizedRewriteRule implements RewriteRuleInterface
{
    protected $hash;
    protected $prefixedQueryArray;
    protected $query;
    protected $queryArray;
    protected $regex;

    public function __construct(
        string $hash,
        array $prefixedQueryArray,
        string $query,
        array $queryArray,
        string $regex
    ) {
        $this->hash = $hash;
        $this->prefixedQueryArray = $prefixedQueryArray;
        $this->query = $query;
        $this->queryArray = $queryArray;
        $this->regex = $regex;
    }

    public function getHash(): string
    {
        return $this->hash;
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
}
