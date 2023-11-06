<?php

declare(strict_types=1);

namespace ToyWpRouting;

use ToyWpRouting\Exception\RequiredQueryVariablesMissingException;

class Rewrite
{
    /**
     * @var mixed
     */
    protected $handler;

    /**
     * @var mixed
     */
    protected $isActiveCallback;

    /**
     * @var array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS">
     */
    protected array $methods;

    protected string $regex;

    protected string $query;

    protected array $queryVariables;

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param mixed $handler
     * @param mixed $isActiveCallback
     */
    public function __construct(array $methods, string $regex, string $query, $handler, string $prefix = '', $isActiveCallback = null)
    {
        Support::assertValidMethodsList($methods);

        $this->methods = $methods;
        $this->regex = $regex;
        $this->handler = $handler;
        $this->isActiveCallback = $isActiveCallback;

        $queryArray = '' === $query ? ['__routeType' => 'static'] : Support::parseQuery($query);
        $prefixedQueryArray = Support::applyPrefixToKeys($queryArray, $prefix);

        $this->query = Support::buildQuery($prefixedQueryArray);
        $this->queryVariables = array_combine(array_keys($prefixedQueryArray), array_keys($queryArray));
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return mixed
     */
    public function getIsActiveCallback()
    {
        return $this->isActiveCallback;
    }

    /**
     * @return array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS">
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function getRequiredQueryVariables(): array
    {
        return array_keys($this->queryVariables);
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getQueryVariables(): array
    {
        return $this->queryVariables;
    }

    public function mapQueryVariable(string $prefixedQueryVariable): ?string
    {
        return $this->queryVariables[$prefixedQueryVariable] ?? null;
    }

    /**
     * @param mixed $isActiveCallback
     */
    public function setIsActiveCallback($isActiveCallback): self
    {
        $this->isActiveCallback = $isActiveCallback;

        return $this;
    }

    public function validate(array $queryVariables): array
    {
        $missing = array_diff_key(array_flip($this->getRequiredQueryVariables()), $queryVariables);

        if ([] !== $missing) {
            throw new RequiredQueryVariablesMissingException(array_keys($missing));
        }

        return $queryVariables;
    }
}
