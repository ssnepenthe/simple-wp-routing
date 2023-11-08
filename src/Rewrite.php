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

    /**
     * @var array<string, string>
     */
    protected array $queryVariables;

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param array<string, string> $queryVariables
     * @param mixed $handler
     * @param mixed $isActiveCallback
     */
    public function __construct(
        array $methods,
        string $regex,
        string $query,
        array $queryVariables,
        $handler,
        $isActiveCallback = null
    ) {
        // @todo Verify query and queryVariables are not empty?
        Support::assertValidMethodsList($methods);

        $this->methods = $methods;
        $this->regex = $regex;
        $this->query = $query;
        $this->queryVariables = $queryVariables;
        $this->handler = $handler;
        $this->isActiveCallback = $isActiveCallback;
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

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getQueryVariables(): array
    {
        return $this->queryVariables;
    }

    /**
     * @param mixed $isActiveCallback
     */
    public function setIsActiveCallback($isActiveCallback): self
    {
        $this->isActiveCallback = $isActiveCallback;

        return $this;
    }

    public function getConcernedQueryVariablesWithoutPrefix(array $queryVariables): array
    {
        $return = $missing = [];

        foreach ($this->queryVariables as $prefixed => $unprefixed) {
            if (! array_key_exists($prefixed, $queryVariables)) {
                $missing[] = $prefixed;
            } else {
                $return[$unprefixed] = $queryVariables[$prefixed];
            }
        }

        if ([] !== $missing) {
            throw new RequiredQueryVariablesMissingException($missing);
        }

        return $return;
    }
}
