<?php

declare(strict_types=1);

namespace ToyWpRouting\Support;

use InvalidArgumentException;
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

    protected string $query;

    /**
     * @var array<string, string>
     */
    protected array $queryVariables;

    protected string $regex;

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
        Support::assertValidMethodsList($methods);

        if ('' === $query) {
            throw new InvalidArgumentException('$query must be a non-empty string');
        }

        $this->methods = $methods;
        $this->regex = $regex;
        $this->query = $query;
        $this->queryVariables = $queryVariables;
        $this->handler = $handler;
        $this->isActiveCallback = $isActiveCallback;
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
        if (null === $this->isActiveCallback) {
            return fn () => true;
        }

        return $this->isActiveCallback;
    }

    /**
     * @return array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS">
     */
    public function getMethods(): array
    {
        return $this->methods;
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

    public function hasIsActiveCallback(): bool
    {
        return null !== $this->isActiveCallback;
    }

    /**
     * @param mixed $isActiveCallback
     */
    public function setIsActiveCallback($isActiveCallback): self
    {
        $this->isActiveCallback = $isActiveCallback;

        return $this;
    }
}
