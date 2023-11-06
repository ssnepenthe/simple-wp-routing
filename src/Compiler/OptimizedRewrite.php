<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use RuntimeException;
use ToyWpRouting\Rewrite;

class OptimizedRewrite extends Rewrite
{
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
        $this->methods = $methods;
        $this->regex = $regex;
        $this->query = $query;
        $this->queryVariables = $queryVariables;
        $this->handler = $handler;
        $this->isActiveCallback = $isActiveCallback;
    }

    public function setIsActiveCallback($isActiveCallback): Rewrite
    {
        throw new RuntimeException('Cannot override isActiveCallback on OptimizedRewrite instance');
    }
}
