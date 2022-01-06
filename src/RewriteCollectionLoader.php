<?php

namespace ToyWpRouting;

use InvalidArgumentException;

class RewriteCollectionLoader
{
    protected $cacheDir;
    protected $cacheFile;
    protected $routeConverter;

    public function __construct(string $cacheDir, string $cacheFile = 'rewrite-cache.php')
    {
        $this->cacheDir = $cacheDir;
        $this->cacheFile = $cacheFile;
    }

    public function fromArray(array $rewrites): RewriteCollection
    {
        $rewriteCollection = new RewriteCollection();

        foreach ($rewrites as $rewriteArray) {
            if (! $this->arrayHasRewriteShape($rewriteArray)) {
                $requiredKeys = [
                    'method',
                    'regex',
                    'handler',
                    'prefixedToUnprefixedQueryVariablesMap',
                    'query',
                    'queryVariables',
                    'isActiveCallback',
                ];
                throw new InvalidArgumentException(
                    'Rewrite array must have all keys: ' . implode(', ', $requiredKeys)
                );
            }

            $rewrite = new OptimizedRewrite(
                $rewriteArray['method'],
                $rewriteArray['regex'],
                $rewriteArray['handler'],
                $rewriteArray['prefixedToUnprefixedQueryVariablesMap'],
                $rewriteArray['query'],
                $rewriteArray['queryVariables']
            );

            $rewrite->setIsActiveCallback($rewriteArray['isActiveCallback']);

            $rewriteCollection->add($rewrite);
        }

        // @todo lock?

        return $rewriteCollection;
    }

    public function fromCache(): RewriteCollection
    {
        $rewrites = array_map(function (array $rewrite) {
            if (
                array_key_exists('handler', $rewrite)
                && $this->isSerializedClosure($rewrite['handler'])
            ) {
                $rewrite['handler'] = unserialize($rewrite['handler'])->getClosure();
            }

            if (
                array_key_exists('isActiveCallback', $rewrite)
                && $this->isSerializedClosure($rewrite['isActiveCallback'])
            ) {
                $rewrite['isActiveCallback'] = unserialize(
                    $rewrite['isActiveCallback']
                )->getClosure();
            }

            return $rewrite;
        }, static::staticIncludeAndReturn("{$this->cacheDir}/{$this->cacheFile}"));

        return $this->fromArray($rewrites);
    }

    public function fromRouteCollection(RouteCollection $routeCollection): RewriteCollection
    {
        $rewriteCollection = $this->getRouteConverter()->convertMany($routeCollection);

        $routeCollection->lock();
        $rewriteCollection->lock();

        return $rewriteCollection;
    }

    public function getRouteConverter(): RouteConverter
    {
        // @todo Interface.
        if (! $this->routeConverter instanceof RouteConverter) {
            $this->routeConverter = $this->createRouteConverter();
        }

        return $this->routeConverter;
    }

    public function hasCachedRewrites()
    {
        return is_readable("{$this->cacheDir}/{$this->cacheFile}");
    }

    public function setRouteConverter(RouteConverter $routeConverter)
    {
        $this->routeConverter = $routeConverter;

        return $this;
    }

    protected function arrayHasRewriteShape($rewriteArray)
    {
        return is_array($rewriteArray)
            && array_key_exists('handler', $rewriteArray)
            && array_key_exists('isActiveCallback', $rewriteArray)
            && array_key_exists('method', $rewriteArray)
            && array_key_exists('prefixedToUnprefixedQueryVariablesMap', $rewriteArray)
            && array_key_exists('query', $rewriteArray)
            && array_key_exists('queryVariables', $rewriteArray)
            && array_key_exists('regex', $rewriteArray);
    }

    protected function createRouteConverter(): RouteConverter
    {
        return new RouteConverter();
    }

    protected function isSerializedClosure($value)
    {
        return is_string($value)
            && 'C:32:"Opis\Closure\SerializableClosure"' === substr($value, 0, 39);
    }

    protected static function staticIncludeAndReturn(string $file)
    {
        return include $file;
    }
}
