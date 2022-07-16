<?php

declare(strict_types=1);

namespace ToyWpRouting;

// @todo Tests?
class OptimizedRewriteRule implements RewriteRuleInterface
{
    protected string $hash;

    protected string $query;

    /**
     * @var array<string, string>
     */
    protected array $queryVariables;

    protected string $regex;

    /**
     * @param array<string, string> $queryVariables
     */
    public function __construct(
        string $hash,
        string $query,
        array $queryVariables,
        string $regex
    ) {
        $this->hash = $hash;
        $this->query = $query;
        $this->queryVariables = $queryVariables;
        $this->regex = $regex;
    }

    public function getHash(): string
    {
        return $this->hash;
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
