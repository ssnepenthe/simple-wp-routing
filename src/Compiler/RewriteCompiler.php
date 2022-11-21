<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use Closure;
use RuntimeException;
use ToyWpRouting\Rewrite;

class RewriteCompiler
{
    private const TEMPLATE = 'new \\ToyWpRouting\\Compiler\\OptimizedRewrite(%s, %s, $this->invocationStrategy, %s, %s)';

    private Rewrite $rewrite;

    public function __construct(Rewrite $rewrite)
    {
        $this->rewrite = $rewrite;
    }

    public function __toString(): string
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return sprintf(
            self::TEMPLATE,
            $this->methods(),
            $this->queryVariables(),
            $this->handler(),
            $this->isActiveCallback()
        );
    }

    /**
     * @param mixed $value
     */
    private function compileCallbackIfSupported($value): string
    {
        if ($value instanceof Closure) {
            return (string) (new ClosureCompiler($value));
        }

        if (is_string($value) || (
            is_array($value)
            && isset($value[0])
            && is_string($value[0])
            && isset($value[1])
            && is_string($value[1])
        )) {
            return var_export($value, true);
        }

        throw new RuntimeException(
            'Unsupported callback type - must be closure, string, or array{0: string, 1: string}'
        );
    }

    private function handler(): string
    {
        return $this->compileCallbackIfSupported($this->rewrite->getHandler());
    }

    private function isActiveCallback(): string
    {
        $isActiveCallback = $this->rewrite->getIsActiveCallback();

        if (null === $isActiveCallback) {
            return var_export($isActiveCallback, true);
        }

        return $this->compileCallbackIfSupported($isActiveCallback);
    }

    private function methods(): string
    {
        return var_export($this->rewrite->getMethods(), true);
    }

    private function queryVariables(): string
    {
        $queryVariables = [];

        foreach ($this->rewrite->getRules() as $rule) {
            foreach ($rule->getQueryVariables() as $prefixed => $unprefixed) {
                $queryVariables[$prefixed] = $unprefixed;
            }
        }

        return var_export($queryVariables, true);
    }
}
