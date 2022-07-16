<?php

declare(strict_types=1);

namespace ToyWpRouting;

class RewriteRule implements RewriteRuleInterface
{
    protected string $prefix = '';

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

    protected string $rawQuery;

    protected string $regex;

    public function __construct(string $regex, string $query, string $prefix = '')
    {
        $this->regex = $regex;
        $this->rawQuery = $query;
        $this->prefix = $prefix;

        $this->parseQuery();
    }

    public function getHash(): string
    {
        return md5($this->regex);
    }

    /**
     *
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
     *
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

    protected function parseQuery(): void
    {
        $queryArray = '' === $this->rawQuery ? [] : Support::parseQuery($this->rawQuery);

        $queryArray['matchedRule'] = $this->getHash();

        $prefixedQueryArray = Support::applyPrefixToKeys($queryArray, $this->prefix);

        $query = Support::buildQuery($prefixedQueryArray);

        $this->prefixedQueryArray = $prefixedQueryArray;
        $this->query = $query;
        $this->queryArray = $queryArray;
        $this->queryVariables = array_combine(
            array_keys($prefixedQueryArray),
            array_keys($queryArray)
        );
    }
}
