<?php

declare(strict_types=1);

namespace ToyWpRouting;

class RouteConverter
{
    protected $parser;

    public function __construct(?RouteParserInterface $parser = null)
    {
        $this->parser = $parser ?: new FastRouteRouteParser();
    }

    public function convert(Route $route): RewriteInterface
    {
        return new Rewrite(
            $route->getMethods(),
            $this->parser->parse($route->getRoute()),
            $route->getHandler(),
            $route->getPrefix(),
            $route->getIsActiveCallback()
        );
    }

    public function convertCollection(RouteCollection $routeCollection): RewriteCollection
    {
        $rewriteCollection = new RewriteCollection();

        foreach ($routeCollection->getRoutes() as $route) {
            $rewriteCollection->add($this->convert($route));
        }

        return $rewriteCollection;
    }
}
