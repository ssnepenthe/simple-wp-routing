<?php

declare(strict_types=1);

namespace SimpleWpRouting\Support;

use LogicException;
use RuntimeException;

class RewriteCollection
{
    protected bool $locked = false;

    /**
     * @var array<string, string>
     */
    protected array $queryVariables = [];

    /**
     * @var array<string, string>
     */
    protected array $rewriteRules = [];

    /**
     * @var Rewrite[]
     */
    protected array $rewrites = [];

    /**
     * @var array<string, array<"GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS", Rewrite>>
     */
    protected array $rewritesByRegexAndMethod = [];

    public function add(Rewrite $rewrite): Rewrite
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add rewrites when rewrite collection is locked');
        }

        $regex = $rewrite->getRegex();

        $this->rewriteRules[$regex] = $rewrite->getQuery();

        foreach ($rewrite->getQueryVariables() as $prefixed => $unprefixed) {
            $this->queryVariables[$prefixed] = $unprefixed;
        }

        if (! array_key_exists($regex, $this->rewritesByRegexAndMethod)) {
            $this->rewritesByRegexAndMethod[$regex] = [];
        }

        foreach ($rewrite->getMethods() as $method) {
            if (array_key_exists($method, $this->rewritesByRegexAndMethod[$regex])) {
                throw new LogicException("Route matching {$regex} for method {$method} already registered");
            }

            $this->rewritesByRegexAndMethod[$regex][$method] = $rewrite;
        }

        $this->rewrites[] = $rewrite;

        return $rewrite;
    }

    public function empty(): bool
    {
        return empty($this->rewrites);
    }

    public function findByRegex(string $regex): array
    {
        if (! array_key_exists($regex, $this->rewritesByRegexAndMethod)) {
            return [];
        }

        return $this->rewritesByRegexAndMethod[$regex];
    }

    /**
     * @return string[]
     */
    public function getQueryVariables(): array
    {
        return array_keys($this->queryVariables);
    }

    /**
     * @return array<string, string>
     */
    public function getRewriteRules(): array
    {
        return $this->rewriteRules;
    }

    /**
     * @return Rewrite[]
     */
    public function getRewrites(): array
    {
        return $this->rewrites;
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
}
