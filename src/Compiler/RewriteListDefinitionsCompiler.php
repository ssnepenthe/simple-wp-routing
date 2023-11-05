<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use ToyWpRouting\Rewrite;

class RewriteListDefinitionsCompiler
{
    /**
     * @var Rewrite[]
     */
    private array $rewrites;

    public function __construct(array $rewrites)
    {
        $this->rewrites = (fn (Rewrite ...$rewrites) => $rewrites)(...$rewrites);
    }

    public function __toString(): string
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return vsprintf($this->prepareTemplate(), array_map(
            fn (Rewrite $rewrite) => (string) (new RewriteCompiler($rewrite)),
            $this->rewrites
        ));
    }

    private function prepareTemplate(): string
    {
        $definitions = $assignments = $byRegexAndMethod = [];

        foreach ($this->rewrites as $i => $rewrite) {
            $definitions[] = "\$rewrite{$i} = %s;";
            $assignments[] = "\$this->rewrites->attach(\$rewrite{$i});";

            foreach ($rewrite->getRules() as $rule) {
                $byRegexAndMethod[$rule->getRegex()] = $byRegexAndMethod[$rule->getRegex()] ?? [];

                foreach ($rewrite->getMethods() as $method) {
                    $byRegexAndMethod[$rule->getRegex()][$method] = "\$rewrite{$i}";
                }
            }
        }

        $definitionsTemplate = implode(PHP_EOL, $definitions);
        $assignmentsTemplate = implode(PHP_EOL, $assignments);
        $byRegexAndMethodTemplate = sprintf('$this->rewritesByRegexAndMethod = %s;', preg_replace(
            '/\'\$rewrite(\d+)\'/',
            '\$rewrite\1',
            var_export($byRegexAndMethod, true)
        ));

        return $definitionsTemplate . PHP_EOL . $assignmentsTemplate . PHP_EOL . $byRegexAndMethodTemplate;
    }
}
