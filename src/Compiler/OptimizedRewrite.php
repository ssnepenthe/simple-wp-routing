<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use RuntimeException;
use ToyWpRouting\InvocationStrategyInterface;
use ToyWpRouting\Rewrite;

class OptimizedRewrite extends Rewrite
{
    /**
     * @var array<string, string>
     */
    protected array $queryVariables;

    /**
     * @var string[]
     */
    protected array $requiredQueryVariables;

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param array<string, string> $queryVariables
     * @param mixed $handler
     * @param mixed $isActiveCallback
     */
    public function __construct(
        array $methods,
        array $queryVariables,
        array $requiredQueryVariables,
        InvocationStrategyInterface $invocationStrategy,
        $handler,
        $isActiveCallback = null
    ) {
        parent::__construct($methods, [], $handler, $isActiveCallback);

        $this->queryVariables = $queryVariables;
        $this->requiredQueryVariables = $requiredQueryVariables;
        $this->invocationStrategy = $invocationStrategy;
    }

    public function getRequiredQueryVariables(): array
    {
        return $this->requiredQueryVariables;
    }

    public function mapQueryVariable(string $queryVariable): ?string
    {
        return $this->queryVariables[$queryVariable] ?? null;
    }

    public function setInvocationStrategy(InvocationStrategyInterface $invocationStrategy): Rewrite
    {
        throw new RuntimeException(
            'Cannot override invocationStrategy on OptimizedRewrite instance'
        );
    }

    public function setIsActiveCallback($isActiveCallback): Rewrite
    {
        throw new RuntimeException('Cannot override isActiveCallback on OptimizedRewrite instance');
    }
}
