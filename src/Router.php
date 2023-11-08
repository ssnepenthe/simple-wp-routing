<?php

declare(strict_types=1);

namespace ToyWpRouting;

final class Router
{
    private bool $autoSlash = true;
    private bool $cacheEnabled = false;

    private string $currentGroup = '';
    private bool $initialized = false;

    private ?CallableResolverInterface $callableResolver = null;
    private ?InvocationStrategyInterface $invocationStrategy = null;
    private ?RouteParserInterface $parser = null;
    private string $prefix = '';
    private ?RewriteCollection $rewriteCollection = null;
    private ?RewriteCollectionCache $rewriteCollectionCache = null;

    public function any(string $route, $handler): Rewrite
    {
        return $this->add(
            $this->create(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $route, $handler)
        );
    }

    public function delete(string $route, $handler): Rewrite
    {
        return $this->add(
            $this->create(['DELETE'], $route, $handler)
        );
    }

    public function get(string $route, $handler): Rewrite
    {
        return $this->add(
            $this->create(['GET', 'HEAD'], $route, $handler)
        );
    }

    public function group(string $group, callable $callback)
    {
        $previousGroup = $this->currentGroup;
        $this->currentGroup = $this->autoSlash($previousGroup, $group);

        $callback($this);

        $this->currentGroup = $previousGroup;
    }

    public function initialize(callable $callback)
    {
        if ($this->initialized) {
            // @todo Throw?
            return;
        }

        $this->initialized = true;

        if ($this->cacheEnabled) {
            if ($this->rewriteCollectionCache()->exists()) {
                $this->loadRewritesFromCache();
            } else {
                $callback($this);

                // @todo Is this the correct action name?
                add_action('shutdown', function () {
                    // @todo Should we have some sort of "cacheNeedsSave" flag?
                    $this->rewriteCollectionCache()->put($this->rewriteCollection());
                });
            }
        } else {
            $callback($this);
        }

        $this->createOrchestrator()->initialize();
    }

    public function options(string $route, $handler): Rewrite
    {
        return $this->add(
            $this->create(['OPTIONS'], $route, $handler)
        );
    }

    public function patch(string $route, $handler): Rewrite
    {
        return $this->add(
            $this->create(['PATCH'], $route, $handler)
        );
    }

    public function post(string $route, $handler): Rewrite
    {
        return $this->add(
            $this->create(['POST'], $route, $handler)
        );
    }

    public function put(string $route, $handler): Rewrite
    {
        return $this->add(
            $this->create(['PUT'], $route, $handler)
        );
    }

    public function rewriteCollection(): RewriteCollection
    {
        if (! $this->rewriteCollection instanceof RewriteCollection) {
            $this->rewriteCollection = new RewriteCollection();
        }

        return $this->rewriteCollection;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    private function add(Rewrite $rewrite): Rewrite
    {
        // @todo Return some sort of parsed route helper object?
        return $this->rewriteCollection()->add($rewrite);
    }

    // @todo method to allow user to define custom methods list
    // @todo any reason for head method?

    private function autoSlash(string $left, string $right): string
    {
        if ('' === $left) {
            return $right;
        }

        if (! $this->autoSlash) {
            return $left . $right;
        }

        return rtrim($left, '/') . '/' . ltrim($right, '/');
    }

    private function callableResolver(): CallableResolverInterface
    {
        if (! $this->callableResolver instanceof CallableResolverInterface) {
            $this->callableResolver = new NullCallableResolver();
        }

        return $this->callableResolver;
    }

    private function create(array $methods, string $route, $handler)
    {
        $route = $this->autoSlash($this->currentGroup, $route);

        [$regex, $queryArray] = $this->parser()->parse($route);

        $prefixedQueryArray = Support::applyPrefixToKeys($queryArray, $this->prefix);
        $query = Support::buildQuery($prefixedQueryArray);
        $queryVariables = array_combine(array_keys($prefixedQueryArray), array_keys($queryArray));

        $rewrite = new Rewrite($methods, $regex, $query, $queryVariables, $handler);

        // @todo ParsedRewrite, PendingRewrite, RewriteHelper? whats in a name?
        return $rewrite;
    }

    private function invocationStrategy(): InvocationStrategyInterface
    {
        if (! $this->invocationStrategy instanceof InvocationStrategyInterface) {
            $this->invocationStrategy = new DefaultInvocationStrategy();
        }

        return $this->invocationStrategy;
    }

    private function parser(): RouteParserInterface
    {
        if (! $this->parser instanceof RouteParserInterface) {
            $this->parser = new FastRouteRouteParser();
        }

        return $this->parser;
    }

    private function rewriteCollectionCache(): RewriteCollectionCache
    {
        if (! $this->rewriteCollectionCache instanceof RewriteCollectionCache) {
            $this->rewriteCollectionCache = new RewriteCollectionCache('', ''); // @todo
        }

        return $this->rewriteCollectionCache;
    }

    private function createOrchestrator(): Orchestrator
    {
        return new Orchestrator(
            $this->rewriteCollection(),
            $this->invocationStrategy(),
            $this->callableResolver(),
            $this->requestContext()
        );
    }
}
