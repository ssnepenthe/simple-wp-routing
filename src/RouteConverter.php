<?php

namespace ToyWpRouting;

class RouteConverter
{
    protected $parser;

    public function __construct(?RouteParserInterface $parser = null)
    {
        $this->parser = $parser ?: new FastRouteRouteParser();
    }

    public function convert(Route $route): RewriteCollection
    {
        $rewriteCollection = new RewriteCollection();

        $rewrites = $this->parser->parse($route->getRoute());
        $hasIsActiveCallback = null !== $route->getIsActiveCallback();

        foreach ($rewrites as $regex => $queryArray) {
            foreach ($route->getMethods() as $method) {
                $rewrite = new Rewrite(
                    $method,
                    $regex,
                    $queryArray,
                    $route->getHandler(),
                    $route->getPrefix()
                );

                if ($hasIsActiveCallback) {
                    $rewrite->setIsActiveCallback($route->getIsActiveCallback());
                }

                $rewriteCollection->add($rewrite);
            }
        }

        return $rewriteCollection;
    }

    public function convertMany(RouteCollection $routeCollection): RewriteCollection
    {
        $rewriteCollection = new RewriteCollection();

        foreach ($routeCollection->getRoutes() as $route) {
            $rewriteCollection->merge($this->convert($route));
        }

        return $rewriteCollection;
    }
}
