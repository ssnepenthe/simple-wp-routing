<?php

declare(strict_types=1);

namespace ToyWpRouting;

// @todo Tests?
class OptimizedRewriteRule implements RewriteRuleInterface
{
    protected string $hash;

    /**
     * @var array<string, string>
     */
    protected array $prefixedQueryArray;

    protected string $query;

    /**
     * @var array<string, string>
     */
    protected array $queryArray;

    /**
     * @var array<string, string>
     */
    protected array $queryVariables;

    protected string $regex;

    /**
     * @param array<string, string> $prefixedQueryArray
     * @param array<string, string> $queryArray
     * @param array<string, string> $queryVariables
     */
    public function __construct(
        string $hash,
        array $prefixedQueryArray,
        string $query,
        array $queryArray,
        array $queryVariables,
        string $regex
    ) {
        $this->hash = $hash;
        $this->prefixedQueryArray = $prefixedQueryArray;
        $this->query = $query;
        $this->queryArray = $queryArray;
        $this->queryVariables = $queryVariables;
        $this->regex = $regex;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return array<string, string>
     */
    public function getPrefixedQueryArray(): array
    {
        return $this->prefixedQueryArray;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return array<string, string>
     */
    public function getQueryArray(): array
    {
        return $this->queryArray;
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
