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
        $rules = $this->parser->parse($route->getRoute());

        $rewrite = new Rewrite(
            $route->getMethods(),
            array_map(
                fn (string $regex, string $query) => new RewriteRule(
                    $regex,
                    $query,
                    $route->getPrefix()
                ),
                array_keys($rules),
                $rules
            ),
            $route->getHandler()
        );

        if (null !== $isActiveCallback = $route->getIsActiveCallback()) {
            $rewrite->setIsActiveCallback($isActiveCallback);
        }

        return $rewrite;
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
