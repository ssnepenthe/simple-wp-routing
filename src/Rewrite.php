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
     * @var array<string, string>
     */
    protected array $queryVariables = [];

    /**
     * @var array<string, string>
     */
    protected array $rewriteRules = [];

    /**
     * @var RewriteRuleInterface[]
     */
    protected array $rules;

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param RewriteRuleInterface[] $rules
     * @param mixed $handler
     */
    public function __construct(array $methods, array $rules, $handler)
    {
        if (! Support::isValidMethodsList($methods)) {
            throw new InvalidArgumentException('@todo');
        }

        // @todo Create setters for methods and rules instead?
        // @todo assert methods and rules are not empty?
        $this->methods = $methods;
        $this->rules = (fn (RewriteRuleInterface ...$rules) => $rules)(...$rules);
        $this->handler = $handler;

        $this->computeAdditionalState();
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
     * @return array<string, string>
     */
    public function getPrefixedToUnprefixedQueryVariablesMap(): array
    {
        return $this->queryVariables;
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
     * @return RewriteRuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param mixed $isActiveCallback
     */
    public function setIsActiveCallback($isActiveCallback): self
    {
        $this->isActiveCallback = $isActiveCallback;

        return $this;
    }

    protected function computeAdditionalState(): void
    {
        foreach ($this->rules as $rule) {
            // @todo Eliminate rewrite rules at this level? Handled by rewrite collection.
            $this->rewriteRules[$rule->getRegex()] = $rule->getQuery();

            $prefixedQueryVariables = array_keys($rule->getPrefixedQueryArray());
            $queryVariables = array_keys($rule->getQueryArray());

            $count = count($queryVariables);

            // @todo ???
            // assert(count($queryVariables) === count($prefixedQueryVariables));

            // @todo array_combine()??
            for ($i = 0; $i < $count; $i++) {
                $this->queryVariables[$prefixedQueryVariables[$i]] = $queryVariables[$i];
            }
        }
    }
}
