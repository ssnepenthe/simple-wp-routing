<?php

declare(strict_types=1);

namespace ToyWpRouting;

use FastRoute\BadRouteException;
use FastRoute\RouteParser;
use FastRoute\RouteParser\Std;

class FastRouteRouteParser implements RouteParserInterface
{
    protected RouteParser $parser;

    public function __construct(?RouteParser $parser = null)
    {
        $this->parser = $parser ?: new Std();
    }

    /**
     * @return array<string, string>
     */
    public function parse(string $route): array
    {
        if ('' === $route) {
            throw new BadRouteException('Empty routes not allowed');
        }

        // @todo Catch and rethrow fast-route exceptions as package-specific exceptions?
        $parsed = $this->parser->parse($route);
        $rewrites = [];

        foreach ($parsed as $segments) {
            [$regex, $queryArray] = $this->convertSegments($segments);

            $rewrites[$regex] = $queryArray;
        }

        return $rewrites;
    }

    /**
     * @param array<int, string|array{0: string, 1: string}> $segments
     *
     * @return array{0: string, 1: string}
     */
    protected function convertSegments(array $segments): array
    {
        if ('' === $segments[0]) {
            throw new BadRouteException('Empty routes not allowed');
        }

        $regex = '';
        $queryArray = [];
        $position = 1;

        foreach ($segments as $segment) {
            if (\is_string($segment)) {
                $regex .= $segment;

                continue;
            }

            [$name, $pattern] = $segment;

            $regex .= "({$pattern})";
            $queryArray[$name] = "\$matches[{$position}]";
            $position++;
        }

        $regex = "^{$regex}$";

        return [$regex, Support::buildQuery($queryArray)];
    }
}
