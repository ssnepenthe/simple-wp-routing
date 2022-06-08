<?php

declare(strict_types=1);

namespace ToyWpRouting;

class OptimizedRewrite implements RewriteInterface
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
    protected array $prefixedToUnprefixedQueryVariablesMap;

    /**
     * @var string[]
     */
    protected array $queryVariables;

    /**
     * @var array<string, string>
     */
    protected array $rewriteRules;

    /**
     * @var RewriteRuleInterface[]
     */
    protected array $rules;

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param array<string, string> $rewriteRules
     * @param RewriteRuleInterface[] $rules
     * @param mixed $handler
     * @param array<string, string> $prefixedToUnprefixedQueryVariablesMap
     * @param string[] $queryVariables
     * @param mixed $isActiveCallback
     */
    public function __construct(
        array $methods,
        array $rewriteRules,
        array $rules,
        $handler,
        array $prefixedToUnprefixedQueryVariablesMap,
        array $queryVariables,
        $isActiveCallback = null
    ) {
        $this->methods = $methods;
        $this->rewriteRules = $rewriteRules;
        $this->rules = $rules;
        $this->handler = $handler;
        $this->prefixedToUnprefixedQueryVariablesMap = $prefixedToUnprefixedQueryVariablesMap;
        $this->queryVariables = $queryVariables;
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
     * @return array<string, string>
     */
    public function getPrefixedToUnprefixedQueryVariablesMap(): array
    {
        return $this->prefixedToUnprefixedQueryVariablesMap;
    }

    /**
     * @return string[]
     */
    public function getQueryVariables(): array
    {
        return $this->queryVariables;
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
}
