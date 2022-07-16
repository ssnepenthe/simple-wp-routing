<?php

declare(strict_types=1);

namespace ToyWpRouting;

use InvalidArgumentException;

class Rewrite implements RewriteInterface
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

    /**
     * @var RewriteRuleInterface[]
     */
    protected array $rules;

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param RewriteRuleInterface[] $rules
     * @param mixed $handler
     */
    public function __construct(array $methods, array $rules, $handler, $isActiveCallback = null)
    {
        if (! Support::isValidMethodsList($methods)) {
            throw new InvalidArgumentException('@todo');
        }

        $this->methods = $methods;
        $this->rules = (fn (RewriteRuleInterface ...$rules) => $rules)(...$rules);
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

    /**
     * @return RewriteRuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function mapQueryVariable(string $queryVariable): ?string
    {
        foreach ($this->rules as $rule) {
            $queryVariables = $rule->getQueryVariables();

            if (array_key_exists($queryVariable, $queryVariables)) {
                return $queryVariables[$queryVariable];
            }
        }

        return null;
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
