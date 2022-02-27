<?php

namespace ToyWpRouting;

use Invoker\Invoker;
use Invoker\InvokerInterface;
use RuntimeException;

class Container
{
    protected $activeRewriteCollection;
    protected $cacheDir;
    protected $cacheFile;
	protected $invocationStrategy;
    protected $invoker;
    protected $prefix;
    protected $requestContext;
    protected $rewriteCollection;
	protected $rewriteCollectionCache;
    protected $routeCollection;
    protected $routeConverter;

    public function getActiveRewriteCollection(): RewriteCollection
    {
        if (! $this->activeRewriteCollection instanceof RewriteCollection) {
            $this->activeRewriteCollection = $this->getRewriteCollection()
				->filter([$this->getInvocationStrategy(), 'invokeIsActiveCallback']);
        }

        return $this->activeRewriteCollection;
    }

	public function cacheDirIsSet(): bool
	{
		return is_string($this->cacheDir);
	}

    public function getCacheDir(): string
    {
        if (! is_string($this->cacheDir)) {
            throw new RuntimeException('Rewrite cache directory not set');
        }

        return $this->cacheDir;
    }

    public function setCacheDir(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;

        return $this;
    }

    public function getCacheFile(): string
    {
        if (! is_string($this->cacheFile)) {
            $this->cacheFile = 'rewrite-cache.php';
        }

        return $this->cacheFile;
    }

    public function setCacheFile(string $cacheFile)
    {
        $this->cacheFile = $cacheFile;

        return $this;
    }

    public function getInvoker(): InvokerInterface
    {
        if (! $this->invoker instanceof InvokerInterface) {
			if (! class_exists(Invoker::class)) {
				throw new RuntimeException('@todo');
			}

            $this->invoker = new Invoker();
        }

        return $this->invoker;
    }

    public function setInvoker(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;

        return $this;
    }

    public function getPrefix(): string
    {
        if (! is_string($this->prefix)) {
            $this->prefix = '';
        }

        return $this->prefix;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getRequestContext(): RequestContext
    {
        if (! $this->requestContext instanceof RequestContext) {
            $this->requestContext = RequestContext::fromGlobals();
        }

        return $this->requestContext;
    }

    public function setRequestContext(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;

        return $this;
    }

    public function getRewriteCollection(): RewriteCollection
    {
        if (! $this->rewriteCollection instanceof RewriteCollection) {
			if ($this->cacheDirIsSet() && $this->getRewriteCollectionCache()->exists()) {
				$this->rewriteCollection = $this->getRewriteCollectionCache()->get();
			} else {
				$this->rewriteCollection = $this->getRouteConverter()->convertCollection(
					$this->getRouteCollection()
				);

				$this->getRouteCollection()->lock();
				$this->rewriteCollection->lock();
			}
        }

        return $this->rewriteCollection;
    }

	public function resetRewriteCollection()
	{
		$this->rewriteCollection = null;
	}

	// @todo Interface
	public function getRewriteCollectionCache(): RewriteCollectionCache
	{
		if (! $this->rewriteCollectionCache instanceof RewriteCollectionCache) {
			$this->rewriteCollectionCache = new RewriteCollectionCache(
				$this->getCacheDir(),
				$this->getCacheFile()
			);
		}

		return $this->rewriteCollectionCache;
	}

    public function getRouteCollection(): RouteCollection
    {
        if (! $this->routeCollection instanceof RouteCollection) {
            $this->routeCollection = new RouteCollection($this->getPrefix());
        }

        return $this->routeCollection;
    }

    public function setRouteCollection(RouteCollection $routeCollection)
    {
        $this->routeCollection = $routeCollection;

        return $this;
    }

    public function getRouteConverter(): RouteConverter
    {
        if (! $this->routeConverter instanceof RouteConverter) {
            $this->routeConverter = new RouteConverter();
        }

        return $this->routeConverter;
    }

    public function setRouteConverter(RouteConverter $routeConverter)
    {
        $this->routeConverter = $routeConverter;

        return $this;
    }

	public function getInvocationStrategy(): InvocationStrategyInterface
	{
		if (! $this->invocationStrategy instanceof InvocationStrategyInterface) {
			$this->invocationStrategy = class_exists(Invoker::class)
				? new InvokerBackedInvocationStrategy($this->getInvoker())
				: new DefaultInvocationStrategy();
		}

		return $this->invocationStrategy;
	}

	public function setInvocationStrategy(InvocationStrategyInterface $invocationStrategy)
	{
		$this->invocationStrategy = $invocationStrategy;

		return $this;
	}
}
