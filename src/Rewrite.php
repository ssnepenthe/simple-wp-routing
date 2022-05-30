<?php

declare(strict_types=1);

namespace ToyWpRouting;

class Rewrite implements RewriteInterface
{
    protected $handler;
    protected $isActiveCallback;
    protected $methods;
    protected $queryVariables = [];
    protected $rewriteRules = [];
    protected $rules;

    public function __construct(array $methods, array $rules, $handler)
    {
        // @todo Create setters for methods and rules instead?
        // @todo assert methods and rules are not empty?
        $this->methods = array_map(fn (string $method) => strtoupper($method), $methods);
        $this->rules = (fn (RewriteRuleInterface ...$rules) => $rules)(...$rules);
        $this->handler = $handler;

        $this->computeAdditionalState();
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getIsActiveCallback()
    {
        return $this->isActiveCallback;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPrefixedToUnprefixedQueryVariablesMap(): array
    {
        return $this->queryVariables;
    }

    public function getQueryVariables(): array
    {
        return array_keys($this->queryVariables);
    }

    public function getRewriteRules(): array
    {
        return $this->rewriteRules;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

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
