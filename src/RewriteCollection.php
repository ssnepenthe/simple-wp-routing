<?php

declare(strict_types=1);

namespace ToyWpRouting;

use RuntimeException;
use SplObjectStorage;

class RewriteCollection
{
    /**
     * @var array<string, string>
     */
    protected $activeQueryVariables;

    /**
     * @var array<string, string>
     */
    protected $activeRewriteRules;

    /**
     * @var array<string, array<"GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS", RewriteInterface>>
     */
    protected $activeRewritesByRegexHashAndMethod;

    /**
     * @var InvocationStrategyInterface
     */
    protected $invocationStrategy;

    protected bool $locked = false;

    protected string $prefix;

    /**
     * @var array<string, string>
     */
    protected $rewriteRules;

    /**
     * @var SplObjectStorage<RewriteInterface, null>
     */
    protected $rewrites;

    public function __construct(
        string $prefix = '',
        ?InvocationStrategyInterface $invocationStrategy = null
    ) {
        $this->prefix = $prefix;
        $this->invocationStrategy = $invocationStrategy ?: new DefaultInvocationStrategy();
        $this->rewrites = new SplObjectStorage();
    }

    public function add(RewriteInterface $rewrite): RewriteInterface
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add rewrites when rewrite collection is locked');
        }

        $this->rewrites->attach($rewrite);

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
     * @param mixed $handler
     */
    public function get(string $regex, string $query, $handler): RewriteInterface
    {
        return $this->add(
            $this->create(['GET', 'HEAD'], $regex, $query, $handler)
        );
    }

    /**
     * @return string[]
     */
    public function getActiveQueryVariables(): array
    {
        if (null === $this->activeQueryVariables) {
            $this->prepareComputedProperties();
        }

        return array_keys($this->activeQueryVariables);
    }

    /**
     * @return array<string, string>
     */
    public function getActiveRewriteRules(): array
    {
        if (null === $this->activeRewriteRules) {
            $this->prepareComputedProperties();
        }

        return $this->activeRewriteRules;
    }

    /**
     * @return array<"GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS", RewriteInterface>
     */
    public function getActiveRewritesByRegexHash(string $regexHash): array
    {
        if (null === $this->activeRewritesByRegexHashAndMethod) {
            $this->prepareComputedProperties();
        }

        if (! array_key_exists($regexHash, $this->activeRewritesByRegexHashAndMethod)) {
            return [];
        }

        return $this->activeRewritesByRegexHashAndMethod[$regexHash];
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return array<string, string>
     */
    public function getRewriteRules(): array
    {
        if (null === $this->rewriteRules) {
            $this->prepareComputedProperties();
        }

        return $this->rewriteRules;
    }

    /**
     * @return SplObjectStorage<RewriteInterface, null>
     */
    public function getRewrites(): SplObjectStorage
    {
        return $this->rewrites;
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
        $rewrite = new Rewrite(
            $methods,
            [new RewriteRule($regex, $query, $this->prefix)],
            $handler
        );

        $rewrite->setInvocationStrategy($this->invocationStrategy);

        return $rewrite;
    }

    protected function prepareComputedProperties()
    {
        if (is_array($this->activeQueryVariables)) {
            return;
        }

        $this->lock();

        $this->activeQueryVariables = [];
        $this->activeRewriteRules = [];
        $this->activeRewritesByRegexHashAndMethod = [];
        $this->rewriteRules = [];

        foreach ($this->rewrites as $rewrite) {
            $isActive = $this->invocationStrategy->invokeIsActiveCallback($rewrite);

            foreach ($rewrite->getRules() as $rule) {
                $this->rewriteRules[$rule->getRegex()] = $rule->getQuery();

                if ($isActive) {
                    $this->activeRewriteRules[$rule->getRegex()] = $rule->getQuery();

                    foreach ($rule->getQueryVariables() as $prefixed => $unprefixed) {
                        $this->activeQueryVariables[$prefixed] = $unprefixed;
                    }

                    $hash = $rule->getHash();

                    if (! array_key_exists($hash, $this->activeRewritesByRegexHashAndMethod)) {
                        $this->activeRewritesByRegexHashAndMethod[$hash] = [];
                    }

                    foreach ($rewrite->getMethods() as $method) {
                        $this->activeRewritesByRegexHashAndMethod[$hash][$method] = $rewrite;
                    }
                }
            }
        }
    }
}
