<?php

declare(strict_types=1);

namespace ToyWpRouting\Dumper;

use ToyWpRouting\Rewrite;

class RewriteListDumper
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
        return $this->dump();
    }

    public function dump(): string
    {
        return vsprintf($this->prepareTemplate(), array_map(
            fn (Rewrite $rewrite) => (string) (new RewriteDumper($rewrite)),
            $this->rewrites
        ));
    }

    private function prepareTemplate(): string
    {
        $definitions = $byRegexAndMethod = [];

        foreach ($this->rewrites as $i => $rewrite) {
            $definitions[] = "\$rewrite{$i} = %s;";

            $byRegexAndMethod[$rewrite->getRegex()] = $byRegexAndMethod[$rewrite->getRegex()] ?? [];

            foreach ($rewrite->getMethods() as $method) {
                $byRegexAndMethod[$rewrite->getRegex()][$method] = "\$rewrite{$i}";
            }
        }

        $definitionsTemplate = implode(PHP_EOL, $definitions);
        $byRegexAndMethodTemplate = sprintf('$this->rewritesByRegexAndMethod = %s;', preg_replace(
            '/\'\$rewrite(\d+)\'/',
            '\$rewrite\1',
            var_export($byRegexAndMethod, true)
        ));

        return $definitionsTemplate . PHP_EOL . $byRegexAndMethodTemplate;
    }
}
