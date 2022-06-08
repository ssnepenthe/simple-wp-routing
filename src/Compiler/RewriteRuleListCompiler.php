<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use ToyWpRouting\RewriteRuleInterface;

class RewriteRuleListCompiler
{
    /**
     * @var RewriteRuleInterface[]
     */
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = (fn (RewriteRuleInterface ...$rules) => $rules)(...$rules);
    }

    public function __toString()
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return vsprintf($this->prepareTemplate(), array_map(
            fn (RewriteRuleInterface $rule) => (string) (new RewriteRuleCompiler($rule)),
            $this->rules
        ));
    }

    private function prepareTemplate()
    {
        return preg_replace(
            '/\'%s\'/',
            '%s',
            var_export(array_fill(0, count($this->rules), '%s'), true)
        );
    }
}
