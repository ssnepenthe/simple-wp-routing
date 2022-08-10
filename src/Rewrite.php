<?php

declare(strict_types=1);

namespace ToyWpRouting;

class Rewrite implements RewriteInterface
{
    /**
     * @var mixed
     */
    protected $handler;

    /**
     * @var InvocationStrategyInterface
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
        Support::assertValidMethodsList($methods);

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

    public function getInvocationStrategy()
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

    /**
     * @return RewriteRuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function handle(array $queryVariables = [])
    {
        return $this->getInvocationStrategy()
            ->withAdditionalContext(['queryVars' => $queryVariables])
            ->invokeHandler($this);
    }

    public function isActive(): bool
    {
        return $this->getInvocationStrategy()->invokeIsActiveCallback($this);
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
}
