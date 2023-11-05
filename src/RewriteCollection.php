<?php

declare(strict_types=1);

namespace ToyWpRouting;

use RuntimeException;
use SplObjectStorage;
use ToyWpRouting\Exception\MethodNotAllowedHttpException;
use ToyWpRouting\Exception\RewriteDisabledException;

class RewriteCollection
{
    protected InvocationStrategyInterface $invocationStrategy;

    protected bool $locked = false;

    protected string $prefix;

    /**
     * @var array<string, string>
     */
    protected array $queryVariables = [];

    /**
     * @var array<string, string>
     */
    protected array $rewriteRules = [];

    /**
     * @var SplObjectStorage<Rewrite, null>
     */
    protected SplObjectStorage $rewrites;

    /**
     * @var array<string, array<"GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS", Rewrite>>
     */
    protected array $rewritesByRegexAndMethod = [];

    public function __construct(
        string $prefix = '',
        ?InvocationStrategyInterface $invocationStrategy = null
    ) {
        $this->prefix = $prefix;
        $this->invocationStrategy = $invocationStrategy ?: new DefaultInvocationStrategy();
        $this->rewrites = new SplObjectStorage();
    }

    public function add(Rewrite $rewrite): Rewrite
    {
        if ($this->locked) {
            throw new RuntimeException('Cannot add rewrites when rewrite collection is locked');
        }

        $this->rewrites->attach($rewrite);

        foreach ($rewrite->getRules() as $rule) {
            $this->rewriteRules[$rule->getRegex()] = $rule->getQuery();

            foreach ($rule->getQueryVariables() as $prefixed => $unprefixed) {
                $this->queryVariables[$prefixed] = $unprefixed;
            }

            $regex = $rule->getRegex();

            if (! array_key_exists($regex, $this->rewritesByRegexAndMethod)) {
                $this->rewritesByRegexAndMethod[$regex] = [];
            }

            foreach ($rewrite->getMethods() as $method) {
                $this->rewritesByRegexAndMethod[$regex][$method] = $rewrite;
            }
        }

        return $rewrite;
    }

    /**
     * @param mixed $handler
     */
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

    /**
     * @param mixed $handler
     */
    public function delete(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['DELETE'], $regex, $query, $handler)
        );
    }

    public function findByRegex(string $regex): array
    {
        if (! array_key_exists($regex, $this->rewritesByRegexAndMethod)) {
            return [];
        }

        return $this->rewritesByRegexAndMethod[$regex];
    }

    /**
     * @param mixed $handler
     */
    public function get(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['GET', 'HEAD'], $regex, $query, $handler)
        );
    }

    /**
     * @return string[]
     */
    public function getQueryVariables(): array
    {
        return array_keys($this->queryVariables);
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
        return $this->rewriteRules;
    }

    /**
     * @return SplObjectStorage<Rewrite, null>
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

    /**
     * @param mixed $handler
     */
    public function options(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['OPTIONS'], $regex, $query, $handler)
        );
    }

    /**
     * @param mixed $handler
     */
    public function patch(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['PATCH'], $regex, $query, $handler)
        );
    }

    /**
     * @param mixed $handler
     */
    public function post(string $regex, string $query, $handler): Rewrite
    {
        return $this->add(
            $this->create(['POST'], $regex, $query, $handler)
        );
    }

    /**
     * @param mixed $handler
     */
    public function put(string $regex, string $query, $handler): Rewrite
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
        $rule = new RewriteRule($regex, $query, $this->prefix);
        $rule->setRequiredQueryVariables(array_keys($rule->getQueryVariables()));

        $rewrite = new Rewrite($methods, [$rule], $handler);
        $rewrite->setInvocationStrategy($this->invocationStrategy);

        return $rewrite;
    }
}
