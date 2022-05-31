<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use Closure;
use RuntimeException;
use ToyWpRouting\RewriteInterface;

class RewriteCompiler
{
    private const TEMPLATE = 'new \\ToyWpRouting\\OptimizedRewrite(%s, %s, %s, %s, %s, %s, %s)';

    private RewriteInterface $rewrite;

    public function __construct(RewriteInterface $rewrite)
    {
        $this->rewrite = $rewrite;
    }

    public function __toString()
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return sprintf(
            self::TEMPLATE,
            $this->methods(),
            $this->rewriteRules(),
            $this->rules(),
            $this->handler(),
            $this->prefixedToUnprefixedQueryVariablesMap(),
            $this->queryVariables(),
            $this->isActiveCallback()
        );
    }

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

        throw new RuntimeException('@todo');
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

    private function prefixedToUnprefixedQueryVariablesMap(): string
    {
        return var_export($this->rewrite->getPrefixedToUnprefixedQueryVariablesMap(), true);
    }

    private function queryVariables(): string
    {
        return var_export($this->rewrite->getQueryVariables(), true);
    }

    private function rewriteRules(): string
    {
        return var_export($this->rewrite->getRewriteRules(), true);
    }

    private function rules(): string
    {
        return (string) (new RewriteRuleListCompiler($this->rewrite->getRules()));
    }
}
