<?php

declare(strict_types=1);

namespace ToyWpRouting;

use LogicException;

final class Router
{
    private bool $autoSlash = true;

    private string $cacheDirectory = '';
    private string $currentGroup = '';
    private bool $initialized = false;

    private ?CallableResolverInterface $callableResolver = null;
    private ?InvocationStrategyInterface $invocationStrategy = null;
    private ?RouteParserInterface $parser = null;
    private string $prefix = '';
    private ?RewriteCollection $rewriteCollection = null;
    private ?RewriteCollectionCache $rewriteCollectionCache = null;

    public function add(array $methods, string $route, $handler): Rewrite
    {
        // @todo Return some sort of parsed route helper object?
        return $this->rewriteCollection()->add($this->create($methods, $route, $handler));
    }

    public function any(string $route, $handler): Rewrite
    {
        return $this->add(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $route, $handler);
    }

    public function delete(string $route, $handler): Rewrite
    {
        return $this->add(['DELETE'], $route, $handler);
    }

    public function enableCache(string $directory): void
    {
        $this->cacheDirectory = $directory;
    }

    public function get(string $route, $handler): Rewrite
    {
        return $this->add(['GET', 'HEAD'], $route, $handler);
    }

    public function group(string $group, callable $callback)
    {
        $previousGroup = $this->currentGroup;
        $this->currentGroup = $this->autoSlash($previousGroup, $group);

        $callback($this);

        $this->currentGroup = $previousGroup;
    }

    public function initialize(?callable $callback = null)
    {
        if ($this->initialized) {
            throw new LogicException('Router already initialized');
        }

        $this->initialized = true;

        if (is_callable($callback)) {
            if ('' !== $this->cacheDirectory) {
                if ($this->rewriteCollection instanceof RewriteCollection) {
                    throw new LogicException('@todo');
                }

                if ($this->rewriteCollectionCache()->exists()) {
                    $this->rewriteCollection = $this->rewriteCollectionCache()->get();
                } else {
                    // @todo RewriteCollection must be empty at this point - should be fine since we verified above that it hasn't been instantiated yet.
                    $callback($this);

                    add_action('shutdown', function () {
                        $this->rewriteCollectionCache()->put($this->rewriteCollection());
                    });
                }
            } else {
                $callback($this);
            }
        } else {
            if ('' !== $this->cacheDirectory) {
                throw new LogicException('$callback must be callable to use cache');
            }

            if ($this->rewriteCollection()->empty()) {
                throw new LogicException('All routes must be registered before calling initialize method');
            }
        }

        $this->rewriteCollection()->lock();
        $this->createOrchestrator()->initialize();
    }

    public function options(string $route, $handler): Rewrite
    {
        return $this->add(['OPTIONS'], $route, $handler);
    }

    public function patch(string $route, $handler): Rewrite
    {
        return $this->add(['PATCH'], $route, $handler);
    }

    public function post(string $route, $handler): Rewrite
    {
        return $this->add(['POST'], $route, $handler);
    }

    public function put(string $route, $handler): Rewrite
    {
        return $this->add(['PUT'], $route, $handler);
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

    public function rewriteCollectionCache(): RewriteCollectionCache
    {
        if ('' === $this->cacheDirectory) {
            throw new LogicException('Cache directory has not been configured - must call enableCache method first');
        }

        if (! $this->rewriteCollectionCache instanceof RewriteCollectionCache) {
            // @todo Configurable cache file?
            $this->rewriteCollectionCache = new RewriteCollectionCache($this->cacheDirectory);
        }

        return $this->rewriteCollectionCache;
    }

    private function createOrchestrator(): Orchestrator
    {
        return new Orchestrator(
            $this->rewriteCollection(),
            $this->invocationStrategy(),
            $this->callableResolver(),
            RequestContext::fromGlobals()
        );
    }
}
