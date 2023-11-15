<?php

declare(strict_types=1);

namespace ToyWpRouting\Dumper;

use ToyWpRouting\Support\RewriteCollection;

final class RewriteCollectionDumper
{
    private string $className = '';

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
            $this->template(),
            $this->className(),
            $this->className(),
            $this->queryVariables(),
            $this->rewriteRules(),
            $this->rewrites(),
            $this->className()
        );
    }

    private function className(): string
    {
        if ('' === $this->className) {
            $this->className = "CachedRewriteCollection{$this->hash()}";
        }

        return $this->className;
    }

    private function hash(): string
    {
        $ctx = hash_init('sha256');

        foreach ($this->rewriteCollection->getRewrites() as $rewrite) {
            foreach ((new RewriteDumper($rewrite))->summary() as $value) {
                hash_update($ctx, $value);
            }
        }

        return hash_final($ctx);
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

    private function template(): string
    {
        return file_get_contents(__DIR__ . '/dumped-rewrite-collection.tpl');
    }
}
