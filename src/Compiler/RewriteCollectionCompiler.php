<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use ToyWpRouting\RewriteCollection;

class RewriteCollectionCompiler
{
    private const TEMPLATE = <<<'TPL'
    <?php

    declare(strict_types=1);

    return new class extends \ToyWpRouting\RewriteCollection
    {
        public function __construct()
        {
            parent::__construct(%s);

            %s

            $this->rewriteRules = %s;
            $this->queryVariables = %s;

            $this->rewritesByRegexHashAndMethod = %s;

            $this->locked = true;
        }
    };

    TPL;

    private RewriteCollection $rewriteCollection;

    public function __construct(RewriteCollection $rewriteCollection)
    {
        $this->rewriteCollection = $rewriteCollection;
    }

    public function __toString()
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return sprintf(
            self::TEMPLATE,
            $this->prefix(),
            $this->rewrites(),
            $this->rewriteRules(),
            $this->queryVariables(),
            $this->rewritesByRegexHashAndMethod()
        );
    }

    private function prefix(): string
    {
        return var_export($this->rewriteCollection->getPrefix(), true);
    }

    private function queryVariables(): string
    {
        return var_export(
            $this->rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap(),
            true
        );
    }

    private function rewriteRules(): string
    {
        return var_export($this->rewriteCollection->getRewriteRules(), true);
    }

    private function rewrites(): string
    {
        return (string) (new RewriteListDefinitionsCompiler(
            iterator_to_array($this->rewriteCollection->getRewrites())
        ));
    }

    private function rewritesByRegexHashAndMethod(): string
    {
        $byRegexHashAndMethod = [];

        foreach ($this->rewriteCollection->getRewrites() as $i => $rewrite) {
            foreach ($rewrite->getRules() as $rule) {
                $byRegexHashAndMethod[$rule->getHash()] = $byRegexHashAndMethod[$rule->getHash()] ?? [];

                foreach ($rewrite->getMethods() as $method) {
                    $byRegexHashAndMethod[$rule->getHash()][$method] = "\$rewrite{$i}";
                }
            }
        }

        $returnString = var_export($byRegexHashAndMethod, true);

        return preg_replace('/\'\$rewrite(\d+)\'/', '\$rewrite\1', $returnString);
    }
}
