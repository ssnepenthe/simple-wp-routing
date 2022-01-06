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
    protected $invoker;
    protected $prefix;
    protected $requestContext;
    protected $rewriteCollection;
    protected $rewriteCollectionDumper;
    protected $rewriteCollectionLoader;
    protected $routeCollection;
    protected $routeConverter;

    public function getActiveRewriteCollection(): RewriteCollection
    {
        if (! $this->activeRewriteCollection instanceof RewriteCollection) {
            $this->activeRewriteCollection = $this->getRewriteCollection()
                ->filterActiveRewrites($this->getInvoker());
        }

        return $this->activeRewriteCollection;
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
            $loader = $this->getRewriteCollectionLoader();

            if ($loader->hasCachedRewrites()) {
                $this->rewriteCollection = $loader->fromCache();
            } else {
                $loader->setRouteConverter($this->getRouteConverter());

                $this->rewriteCollection = $loader->fromRouteCollection(
                    $this->getRouteCollection()
                );
            }
        }

        return $this->rewriteCollection;
    }

    public function getRewriteCollectionDumper(): RewriteCollectionDumper
    {
        if (! $this->rewriteCollectionDumper instanceof RewriteCollectionDumper) {
            $this->rewriteCollectionDumper = new RewriteCollectionDumper(
                $this->getRewriteCollection()
            );
        }

        return $this->rewriteCollectionDumper;
    }

    public function setRewriteCollectionDumper(RewriteCollectionDumper $rewriteCollectionDumper)
    {
        $this->rewriteCollectionDumper = $rewriteCollectionDumper;

        return $this;
    }

    public function getRewriteCollectionLoader(): RewriteCollectionLoader
    {
        if (! $this->rewriteCollectionLoader instanceof RewriteCollectionLoader) {
            $this->rewriteCollectionLoader = new RewriteCollectionLoader(
                $this->getCacheDir(),
                $this->getCacheFile()
            );
        }

        return $this->rewriteCollectionLoader;
    }

    public function setRewriteCollectionLoader(RewriteCollectionLoader $rewriteCollectionLoader)
    {
        $this->rewriteCollectionLoader = $rewriteCollectionLoader;

        return $this;
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
}
