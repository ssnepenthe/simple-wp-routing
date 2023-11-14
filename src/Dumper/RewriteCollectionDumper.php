<?php

declare(strict_types=1);

namespace ToyWpRouting\Dumper;

use ToyWpRouting\Support\RewriteCollection;

class RewriteCollectionDumper
{
    private const TEMPLATE = <<<'TPL'
    <?php

    declare(strict_types=1);

    return function (): \ToyWpRouting\Support\RewriteCollection {
        return new class() extends \ToyWpRouting\Support\RewriteCollection
        {
            protected bool $locked = true;

            public function __construct()
            {
                $this->queryVariables = %s;
                $this->rewriteRules = %s;

                %s
            }

            public function getRewrites(): array
            {
                throw new LogicException('Rewrites list not accessible on cache rewrite collection');
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
        return $this->dump();
    }

    public function dump(): string
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
            foreach ($rewrite->getQueryVariables() as $prefixed => $unprefixed) {
                $queryVariables[$prefixed] = $unprefixed;
            }
        }

        return var_export($queryVariables, true);
    }

    private function rewriteRules(): string
    {
        $rewriteRules = [];

        foreach ($this->rewriteCollection->getRewrites() as $rewrite) {
            $rewriteRules[$rewrite->getRegex()] = $rewrite->getQuery();
        }

        return var_export($rewriteRules, true);
    }

    private function rewrites(): string
    {
        return (string) (new RewriteListDumper($this->rewriteCollection->getRewrites()));
    }
}
