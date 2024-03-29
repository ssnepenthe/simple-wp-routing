<?php

declare(strict_types=1);

namespace SimpleWpRouting;

use LogicException;
use SimpleWpRouting\CallableResolver\CallableResolverInterface;
use SimpleWpRouting\CallableResolver\DefaultCallableResolver;
use SimpleWpRouting\Invoker\DefaultInvoker;
use SimpleWpRouting\Invoker\InvokerInterface;
use SimpleWpRouting\Parser\FastRouteRouteParser;
use SimpleWpRouting\Parser\RouteParserInterface;
use SimpleWpRouting\Support\Orchestrator;
use SimpleWpRouting\Support\RequestContext;
use SimpleWpRouting\Support\Rewrite;
use SimpleWpRouting\Support\RewriteCollection;
use SimpleWpRouting\Support\RewriteCollectionCache;
use SimpleWpRouting\Support\Route;
use SimpleWpRouting\Support\Support;

final class Router
{
    private bool $autoSlash = true;

    private string $cacheDirectory = '';

    private string $cacheFile = '';

    private ?CallableResolverInterface $callableResolver = null;

    private string $currentGroup = '';

    private bool $initialized = false;

    private ?InvokerInterface $invocationStrategy = null;

    private string $prefix = '';

    private ?RequestContext $requestContext = null;

    private ?RewriteCollection $rewriteCollection = null;

    private ?RewriteCollectionCache $rewriteCollectionCache = null;

    private ?RouteParserInterface $routeParser = null;

    /**
     * @param mixed $handler
     */
    public function add(array $methods, string $route, $handler): Route
    {
        $routeInstance = $this->create($methods, $route, $handler);

        foreach ($routeInstance->getRewrites() as $rewrite) {
            $this->getRewriteCollection()->add($rewrite);
        }

        return $routeInstance;
    }

    /**
     * @param mixed $handler
     */
    public function any(string $route, $handler): Route
    {
        return $this->add(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $route, $handler);
    }

    /**
     * @param mixed $handler
     */
    public function delete(string $route, $handler): Route
    {
        return $this->add(['DELETE'], $route, $handler);
    }

    public function disableAutoSlash(): void
    {
        $this->autoSlash = false;
    }

    public function disableCache(): void
    {
        if ($this->initialized || $this->routesHaveBeenAdded()) {
            throw new LogicException('Cache must be disabled before any routes are added and router is initialized');
        }

        $this->cacheDirectory = '';
    }

    public function enableAutoSlash(): void
    {
        $this->autoSlash = true;
    }

    public function enableCache(string $directory, string $file = 'rewrite-cache.php'): void
    {
        if ($this->initialized || $this->routesHaveBeenAdded()) {
            throw new LogicException('Cache must be enabled before any routes are added and router is initialized');
        }

        $this->cacheDirectory = $directory;
        $this->cacheFile = $file;
    }

    /**
     * @param mixed $handler
     */
    public function get(string $route, $handler): Route
    {
        return $this->add(['GET', 'HEAD'], $route, $handler);
    }

    public function getCallableResolver(): CallableResolverInterface
    {
        if (! $this->callableResolver instanceof CallableResolverInterface) {
            $this->callableResolver = new DefaultCallableResolver();
        }

        return $this->callableResolver;
    }

    public function getInvoker(): InvokerInterface
    {
        if (! $this->invocationStrategy instanceof InvokerInterface) {
            $this->invocationStrategy = new DefaultInvoker();
        }

        return $this->invocationStrategy;
    }

    public function getRequestContext(): RequestContext
    {
        if (! $this->requestContext instanceof RequestContext) {
            $this->requestContext = RequestContext::fromGlobals();
        }

        return $this->requestContext;
    }

    public function getRewriteCollection(): RewriteCollection
    {
        if (! $this->rewriteCollection instanceof RewriteCollection) {
            $this->rewriteCollection = new RewriteCollection();
        }

        return $this->rewriteCollection;
    }

    public function getRewriteCollectionCache(): RewriteCollectionCache
    {
        if ('' === $this->cacheDirectory) {
            throw new LogicException('Cache directory has not been configured - must call enableCache method first');
        }

        if (! $this->rewriteCollectionCache instanceof RewriteCollectionCache) {
            $this->rewriteCollectionCache = new RewriteCollectionCache($this->cacheDirectory, $this->cacheFile);
        }

        return $this->rewriteCollectionCache;
    }

    public function getRouteParser(): RouteParserInterface
    {
        if (! $this->routeParser instanceof RouteParserInterface) {
            $this->routeParser = new FastRouteRouteParser();
        }

        return $this->routeParser;
    }

    public function group(string $group, callable $callback): void
    {
        $previousGroup = $this->currentGroup;
        $this->currentGroup = $this->autoSlash($previousGroup, $group);

        $callback($this);

        $this->currentGroup = $previousGroup;
    }

    public function initialize(?callable $callback = null): void
    {
        if ($this->initialized) {
            throw new LogicException('Router already initialized');
        }

        $this->initialized = true;

        if (is_callable($callback)) {
            if ('' !== $this->cacheDirectory) {
                if ($this->routesHaveBeenAdded()) {
                    throw new LogicException('Routes must only be registered within $callback when cache enabled');
                }

                if ($this->getRewriteCollectionCache()->exists()) {
                    $this->rewriteCollection = $this->getRewriteCollectionCache()->get();
                } else {
                    $callback($this);

                    $this->getRewriteCollectionCache()->put($this->getRewriteCollection());
                }
            } else {
                $callback($this);
            }
        } else {
            if ('' !== $this->cacheDirectory) {
                throw new LogicException('$callback must be callable to use cache');
            }

            if ($this->getRewriteCollection()->empty()) {
                throw new LogicException('All routes must be registered before calling initialize method');
            }
        }

        $this->getRewriteCollection()->lock();
        $this->createOrchestrator()->initialize();
    }

    /**
     * @param mixed $handler
     */
    public function options(string $route, $handler): Route
    {
        return $this->add(['OPTIONS'], $route, $handler);
    }

    /**
     * @param mixed $handler
     */
    public function patch(string $route, $handler): Route
    {
        return $this->add(['PATCH'], $route, $handler);
    }

    /**
     * @param mixed $handler
     */
    public function post(string $route, $handler): Route
    {
        return $this->add(['POST'], $route, $handler);
    }

    /**
     * @param mixed $handler
     */
    public function put(string $route, $handler): Route
    {
        return $this->add(['PUT'], $route, $handler);
    }

    public function setCallableResolver(CallableResolverInterface $callableResolver): void
    {
        if ($this->initialized) {
            throw new LogicException('Callable resolver cannot be set after router has been initialized');
        }

        $this->callableResolver = $callableResolver;
    }

    public function setInvocationStrategy(InvokerInterface $invocationStrategy): void
    {
        if ($this->initialized) {
            throw new LogicException('Invocation strategy cannot be set after router has been initialized');
        }

        $this->invocationStrategy = $invocationStrategy;
    }

    public function setPrefix(string $prefix): void
    {
        if ($this->routesHaveBeenAdded()) {
            throw new LogicException('Prefix cannot be changed after routes have been added');
        }

        $this->prefix = $prefix;
    }

    public function setRequestContext(RequestContext $requestContext): void
    {
        if ($this->initialized) {
            throw new LogicException('Request context cannot be set after router has been initialized');
        }

        $this->requestContext = $requestContext;
    }

    public function setRouteParser(RouteParserInterface $routeParser): void
    {
        if ($this->routesHaveBeenAdded()) {
            throw new LogicException('Route parser cannot be changed after routes have been added');
        }

        $this->routeParser = $routeParser;
    }

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

    /**
     * @param mixed $handler
     */
    private function create(array $methods, string $route, $handler): Route
    {
        $route = $this->autoSlash($this->currentGroup, $route);

        $rules = $this->getRouteParser()->parse($route);
        $rewrites = [];

        foreach ($rules as $regex => $queryArray) {
            $prefixedQueryArray = Support::applyPrefixToKeys($queryArray, $this->prefix);

            $query = Support::buildQuery($prefixedQueryArray + [
                // We add an additional __routeType variable to ensure we never have an empty query string.
                // __routeType is not registered as a public query variable with WordPress.
                // If a __routeType variable already exists it is not overwritten.
                Support::applyPrefix('__routeType', $this->prefix) => ([] === $prefixedQueryArray ? 'static' : 'variable')
            ]);

            $queryVariables = array_combine(array_keys($prefixedQueryArray), array_keys($queryArray));

            $rewrites[] = new Rewrite($methods, $regex, $query, $queryVariables, $handler);
        }

        return new Route(...$rewrites);
    }

    private function createOrchestrator(): Orchestrator
    {
        return new Orchestrator(
            $this->getRewriteCollection(),
            $this->getInvoker(),
            $this->getCallableResolver(),
            $this->getRequestContext()
        );
    }

    private function routesHaveBeenAdded(): bool
    {
        return $this->rewriteCollection instanceof RewriteCollection && ! $this->rewriteCollection->empty();
    }
}
