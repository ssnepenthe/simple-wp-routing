<?php

namespace ToyWpRouting;

use Invoker\InvokerInterface;
use RuntimeException;

class RewriteCollection
{
    protected $locked = false;

    protected $queryVariables = [];
    protected $rewriteRules = [];

    protected $rewrites = [];
    protected $rewritesByHashAndMethod = [];

    public function add(RewriteInterface $rewrite)
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add rewrites when rewrite collection is locked');
        }

        $this->rewrites[] = $rewrite;
        $this->rewriteRules[$rewrite->getRegex()] = $rewrite->getQuery();

        foreach ($rewrite->getPrefixedToUnprefixedQueryVariablesMap() as $prefixed => $unprefixed) {
            $this->queryVariables[$prefixed] = $unprefixed;
        }

        $this->addHashAndMethodLookup($rewrite);
    }

    public function addMany(array $rewrites)
    {
        foreach ($rewrites as $rewrite) {
            $this->add($rewrite);
        }
    }

    public function filterActiveRewrites(?InvokerInterface $invoker = null)
    {
        $collection = new self();

        foreach ($this->rewrites as $rewrite) {
            if (! $rewrite->isActive($invoker)) {
                continue;
            }

            $collection->add($rewrite);
        }

        if ($this->locked) {
            $collection->lock();
        }

        return $collection;
    }

    public function getRewritesByRegexHash(string $regexHash): array
    {
        if (! array_key_exists($regexHash, $this->rewritesByHashAndMethod)) {
            return [];
        }

        return $this->rewritesByHashAndMethod[$regexHash];
    }

    public function lock()
    {
        $this->locked = true;
    }

    public function merge(RewriteCollection $rewriteCollection)
    {
        foreach ($rewriteCollection->getRewrites() as $rewrite) {
            $this->add($rewrite);
        }
    }

    public function getPrefixedToUnprefixedQueryVariablesMap()
    {
        return $this->queryVariables;
    }

    public function getQueryVariables()
    {
        return array_keys($this->queryVariables);
    }

    public function getRewrites()
    {
        return $this->rewrites;
    }

    public function getRewriteRules()
    {
        return $this->rewriteRules;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    private function addHashAndMethodLookup(RewriteInterface $rewrite)
    {
        $hash = md5($rewrite->getRegex());
        $method = $rewrite->getMethod();

        if (! array_key_exists($hash, $this->rewritesByHashAndMethod)) {
            $this->rewritesByHashAndMethod[$hash] = [];
        }

        $this->rewritesByHashAndMethod[$hash][strtoupper($method)] = $rewrite;
    }
}
