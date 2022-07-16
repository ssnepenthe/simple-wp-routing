<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use ToyWpRouting\RewriteRuleInterface;

class RewriteRuleCompiler
{
    private const TEMPLATE = 'new \\ToyWpRouting\\OptimizedRewriteRule(%s, %s, %s, %s, %s, %s)';

    private RewriteRuleInterface $rule;

    public function __construct(RewriteRuleInterface $rule)
    {
        $this->rule = $rule;
    }

    public function __toString()
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return sprintf(
            self::TEMPLATE,
            $this->hash(),
            $this->prefixedQueryArray(),
            $this->query(),
            $this->queryArray(),
            $this->queryVariables(),
            $this->regex()
        );
    }

    private function hash(): string
    {
        return var_export($this->rule->getHash(), true);
    }

    private function prefixedQueryArray(): string
    {
        return var_export($this->rule->getPrefixedQueryArray(), true);
    }

    private function query(): string
    {
        return var_export($this->rule->getQuery(), true);
    }

    private function queryArray(): string
    {
        return var_export($this->rule->getQueryArray(), true);
    }

    private function queryVariables(): string
    {
        return var_export($this->rule->getQueryVariables(), true);
    }

    private function regex(): string
    {
        return var_export($this->rule->getRegex(), true);
    }
}
