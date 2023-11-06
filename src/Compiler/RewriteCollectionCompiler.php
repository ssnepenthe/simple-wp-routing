<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use ToyWpRouting\RewriteCollection;

class RewriteCollectionCompiler
{
    private const TEMPLATE = <<<'TPL'
    <?php

    declare(strict_types=1);

    return function (): \ToyWpRouting\RewriteCollection {
        return new class() extends \ToyWpRouting\RewriteCollection
        {
            protected bool $locked = true;

            public function __construct()
            {
                parent::__construct();

                $this->queryVariables = %s;
                $this->rewriteRules = %s;

                %s
            }
        };
    };

    TPL;

    private RewriteCollection $rewriteCollection;

    public function __construct(RewriteCollection $rewriteCollection)
    {
        $this->rewriteCollection = $rewriteCollection;
    }

    public function __toString(): string
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return sprintf(
            self::TEMPLATE,
            $this->queryVariables(),
            $this->rewriteRules(),
            $this->rewrites()
        );
    }

    private function queryVariables(): string
    {
        $queryVariables = [];

        foreach ($this->rewriteCollection->getRewrites() as $rewrite) {
            foreach ($rewrite->getRules() as $rule) {
                foreach ($rule->getQueryVariables() as $prefixed => $unprefixed) {
                    $queryVariables[$prefixed] = $unprefixed;
                }
            }
        }

        return var_export($queryVariables, true);
    }

    private function rewriteRules(): string
    {
        $rewriteRules = [];

        foreach ($this->rewriteCollection->getRewrites() as $rewrite) {
            foreach ($rewrite->getRules() as $rule) {
                $rewriteRules[$rule->getRegex()] = $rule->getQuery();
            }
        }

        return var_export($rewriteRules, true);
    }

    private function rewrites(): string
    {
        return (string) (new RewriteListDefinitionsCompiler(
            iterator_to_array($this->rewriteCollection->getRewrites())
        ));
    }
}
