<?php

declare(strict_types=1);

namespace ToyWpRouting;

use RuntimeException;
use SplObjectStorage;

class RewriteCollection
{
    protected $locked = false;
    protected $prefix;
    protected $queryVariables = [];
    protected $rewriteRules = [];
    /**
     * @var SplObjectStorage<RewriteInterface>
     */
    protected $rewrites;
    protected $rewritesByRegexHashAndMethod = [];

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;

        $this->rewrites = new SplObjectStorage();
    }

    public function add(RewriteInterface $rewrite): RewriteInterface
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add rewrites when rewrite collection is locked');
        }

        $this->rewrites->attach($rewrite);

        //  @todo Minimize loopage?
        $this->rewriteRules = array_merge($this->rewriteRules, $rewrite->getRewriteRules());
        $this->queryVariables = array_merge(
            $this->queryVariables,
            $rewrite->getPrefixedToUnprefixedQueryVariablesMap()
        );

        foreach ($rewrite->getRules() as $rule) {
            $hash = $rule->getHash();

            if (! array_key_exists($hash, $this->rewritesByRegexHashAndMethod)) {
                $this->rewritesByRegexHashAndMethod[$hash] = [];
            }

            foreach ($rewrite->getMethods() as $method) {
                $this->rewritesByRegexHashAndMethod[$hash][$method] = $rewrite;
            }
        }

        return $rewrite;
    }

    public function any(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(
                ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                $regex,
                $query,
                $handler
            )
        );
    }

    public function delete(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['DELETE'], $regex, $query, $handler)
        );
    }

    public function filter(callable $filterFunction)
    {
        $collection = new self($this->prefix);

        foreach ($this->rewrites as $rewrite) {
            if ($filterFunction($rewrite)) {
                $collection->add($rewrite);
            }
        }

        // @todo Should a filtered collection always be locked?
        if ($this->locked) {
            $collection->lock();
        }

        return $collection;
    }

    public function get(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['GET', 'HEAD'], $regex, $query, $handler)
        );
    }

    public function getPrefixedToUnprefixedQueryVariablesMap()
    {
        return $this->queryVariables;
    }

    public function getQueryVariables()
    {
        return array_keys($this->queryVariables);
    }

    public function getRewriteRules()
    {
        return $this->rewriteRules;
    }

    public function getRewrites()
    {
        return $this->rewrites;
    }

    public function getRewritesByRegexHash(string $regexHash): array
    {
        if (! array_key_exists($regexHash, $this->rewritesByRegexHashAndMethod)) {
            return [];
        }

        return $this->rewritesByRegexHashAndMethod[$regexHash];
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    public function options(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['OPTIONS'], $regex, $query, $handler)
        );
    }

    public function patch(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['PATCH'], $regex, $query, $handler)
        );
    }

    public function post(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['POST'], $regex, $query, $handler)
        );
    }

    public function put(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['PUT'], $regex, $query, $handler)
        );
    }

    protected function create(array $methods, string $regex, string $query, $handler): Rewrite
    {
        return new Rewrite($methods, [new RewriteRule($regex, $query, $this->prefix)], $handler);
    }
}
