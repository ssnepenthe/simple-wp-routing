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
     * @var ?InvocationStrategyInterface
     */
    protected $invocationStrategy;

    /**
     * @var mixed
     */
    protected $isActiveCallback;

    /**
     * @var array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS">
     */
    protected array $methods;

    /**
     * @var RewriteRule[]
     */
    protected array $rules;

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param RewriteRule[] $rules
     * @param mixed $handler
     * @param mixed $isActiveCallback
     */
    public function __construct(array $methods, array $rules, $handler, $isActiveCallback = null)
    {
        Support::assertValidMethodsList($methods);

        $this->methods = $methods;
        $this->rules = (fn (RewriteRule ...$rules) => $rules)(...$rules);
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

    public function getInvocationStrategy(): InvocationStrategyInterface
    {
        if (! $this->invocationStrategy instanceof InvocationStrategyInterface) {
            $this->invocationStrategy = new DefaultInvocationStrategy();
        }

        return $this->invocationStrategy;
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

    public function getRequiredQueryVariables(): array
    {
        $requiredQueryVariables = [];

        foreach ($this->rules as $rule) {
            foreach ($rule->getRequiredQueryVariables() as $requiredQueryVariable) {
                $requiredQueryVariables[$requiredQueryVariable] = true;
            }
        }

        return array_keys($requiredQueryVariables);
    }

    /**
     * @return RewriteRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return mixed
     */
    public function handle(array $queryVariables = [])
    {
        $context = [];

        foreach ($queryVariables as $key => $value) {
            if (is_string($newKey = $this->mapQueryVariable($key))) {
                $context[$newKey] = $value;
            }
        }

        return $this->getInvocationStrategy()->invoke($this->getHandler(), $context);
    }

    public function isActive(): bool
    {
        $callback = $this->getIsActiveCallback();

        if (null === $callback) {
            return true;
        }

        return $this->getInvocationStrategy()->invoke($callback);
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

    public function setInvocationStrategy(InvocationStrategyInterface $invocationStrategy): self
    {
        $this->invocationStrategy = $invocationStrategy;

        return $this;
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
