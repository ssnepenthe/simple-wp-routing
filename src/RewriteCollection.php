<?php

namespace ToyWpRouting;

use RuntimeException;

class RewriteCollection
{
    protected $locked = false;
    protected $queryVariables = [];
    protected $rewriteRules = [];
    protected $rewrites = [];
    protected $rewritesByRegexHashAndMethod = [];

    public function add(RewriteInterface $rewrite)
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add rewrites when rewrite collection is locked');
        }

        $this->rewrites[] = $rewrite;
        $this->rewriteRules = array_merge($this->rewriteRules, $rewrite->getRules());
        $this->queryVariables = array_merge(
            $this->queryVariables,
            $rewrite->getPrefixedToUnprefixedQueryVariablesMap()
        );

        foreach ($rewrite->getRules() as $regex => $_) {
            $regexHash = md5($regex);

            if (! array_key_exists($regexHash, $this->rewritesByRegexHashAndMethod)) {
                $this->rewritesByRegexHashAndMethod[$regexHash] = [];
            }

            foreach ($rewrite->getMethods() as $method) {
                $this->rewritesByRegexHashAndMethod[$regexHash][$method] = $rewrite;
            }
        }
    }

	public function filter(callable $filterFunction)
	{
		$collection = new self();

		foreach ($this->rewrites as $rewrite) {
			if ($filterFunction($rewrite)) {
				$collection->add($rewrite);
			}
		}

		if ($this->locked) {
			$collection->lock();
		}

		return $collection;
	}

    public function lock()
    {
        $this->locked = true;
    }

    public function getPrefixedToUnprefixedQueryVariablesMap()
    {
        return $this->queryVariables;
    }

    public function getQueryVariables()
    {
        return array_keys($this->queryVariables);
    }

    public function getRewritesByRegexHash(string $regexHash): array
    {
        if (! array_key_exists($regexHash, $this->rewritesByRegexHashAndMethod)) {
            return [];
        }

        return $this->rewritesByRegexHashAndMethod[$regexHash];
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
}
