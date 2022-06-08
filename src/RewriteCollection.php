<?php

declare(strict_types=1);

namespace ToyWpRouting;

use RuntimeException;
use SplObjectStorage;

class RewriteCollection
{
    protected bool $locked = false;

    protected string $prefix;

    /**
     * @var array<string, string>
     */
    protected array $queryVariables = [];

    /**
     * @var array<string, string>
     */
    protected $rewriteRules = [];

    /**
     * @var SplObjectStorage<RewriteInterface, null>
     */
    protected $rewrites;

    /**
     * @var array<string, array<"GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS", RewriteInterface>>
     */
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

    /**
     * @param mixed $handler
     */
    public function any(string $regex, string $query, $handler): RewriteInterface
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

    /**
     * @param mixed $handler
     */
    public function delete(string $regex, string $query, $handler): RewriteInterface
    {
        return $this->add(
            $this->create(['DELETE'], $regex, $query, $handler)
        );
    }

    /**
     * @param callable(RewriteInterface):bool $filterFunction
     */
    public function filter(callable $filterFunction): self
    {
        $collection = new self($this->prefix);

        foreach ($this->rewrites as $rewrite) {
            if ($filterFunction($rewrite)) {
                // @TODO !!!!!!!!!!!!!
                $collection->add($rewrite);
            }
        }

        // @todo Should a filtered collection always be locked?
        if ($this->locked) {
            $collection->lock();
        }

        return $collection;
    }

    /**
     * @param mixed $handler
     */
    public function get(string $regex, string $query, $handler): RewriteInterface
    {
        return $this->add(
            $this->create(['GET', 'HEAD'], $regex, $query, $handler)
        );
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return array<string, string>
     */
    public function getPrefixedToUnprefixedQueryVariablesMap(): array
    {
        return $this->queryVariables;
    }

    /**
     * @return string[]
     */
    public function getQueryVariables(): array
    {
        return array_keys($this->queryVariables);
    }

    /**
     *
     * @return array<string, string>
     */
    public function getRewriteRules(): array
    {
        return $this->rewriteRules;
    }

    /**
     * @return SplObjectStorage<RewriteInterface, null>
     */
    public function getRewrites(): SplObjectStorage
    {
        return $this->rewrites;
    }

    /**
     * @return array<"GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS", RewriteInterface>
     */
    public function getRewritesByRegexHash(string $regexHash): array
    {
        if (! array_key_exists($regexHash, $this->rewritesByRegexHashAndMethod)) {
            return [];
        }

        return $this->rewritesByRegexHashAndMethod[$regexHash];
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    public function options(string $regex, string $query, $handler): RewriteInterface
    {
        return $this->add(
            $this->create(['OPTIONS'], $regex, $query, $handler)
        );
    }

    public function patch(string $regex, string $query, $handler): RewriteInterface
    {
        return $this->add(
            $this->create(['PATCH'], $regex, $query, $handler)
        );
    }

    public function post(string $regex, string $query, $handler): RewriteInterface
    {
        return $this->add(
            $this->create(['POST'], $regex, $query, $handler)
        );
    }

    public function put(string $regex, string $query, $handler): RewriteInterface
    {
        return $this->add(
            $this->create(['PUT'], $regex, $query, $handler)
        );
    }

    /**
     * @param array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS"> $methods
     * @param mixed $handler
     */
    protected function create(array $methods, string $regex, string $query, $handler): Rewrite
    {
        return new Rewrite($methods, [new RewriteRule($regex, $query, $this->prefix)], $handler);
    }
}
