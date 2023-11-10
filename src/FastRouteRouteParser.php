<?php

declare(strict_types=1);

namespace ToyWpRouting;

use ToyWpRouting\Exception\BadRouteException;

/**
 * Adapted from https://github.com/nikic/FastRoute/blob/181d480e08d9476e61381e04a71b34dc0432e812/src/RouteParser/Std.php
 */
class FastRouteRouteParser implements RouteParserInterface
{
    const VARIABLE_REGEX = <<<'REGEX'
\{
    \s* ([a-zA-Z_][a-zA-Z0-9_-]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}
REGEX;
    const DEFAULT_DISPATCH_REGEX = '[^/]+';

    /**
     * @return array{0: string, 1: array}
     */
    public function parse(string $route): array
    {
        if ('' === $route) {
            throw new BadRouteException('Empty routes not allowed');
        }

        $routeWithoutClosingOptionals = rtrim($route, ']');
        $numOptionals = strlen($route) - strlen($routeWithoutClosingOptionals);

        // Split on [ while skipping placeholders
        $segments = preg_split('~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | \[~x', $routeWithoutClosingOptionals);
        if ($numOptionals !== count($segments) - 1) {
            // If there are any ] in the middle of the route, throw a more specific error message
            if (preg_match('~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | \]~x', $routeWithoutClosingOptionals)) {
                throw new BadRouteException('Optional segments can only occur at the end of a route');
            }
            throw new BadRouteException("Number of opening '[' and closing ']' does not match");
        }

        $currentRoute = '';
        $rewrites = [];
        $finalQuery = [];
        foreach ($segments as $n => $segment) {
            if ($segment === '' && $n !== 0) {
                throw new BadRouteException('Empty optional part');
            }

            $currentRoute .= $segment;
            [$regex, $finalQuery] = $this->convertSegments($this->parsePlaceholders($currentRoute));
            $rewrites[] = $regex;
        }

        return ['^(?|' . implode('|', $rewrites) . ')$', $finalQuery];
    }

    /**
     * @todo Merge convertSegments and parsePlaceholders methods.
     *
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

        $queryArray['__routeType'] = ([] === $queryArray) ? 'static' : 'variable';

        return [$regex, $queryArray];
    }

    /**
     * Parses a route string that does not contain optional segments.
     *
     * @param string
     * @return mixed[]
     */
    private function parsePlaceholders($route)
    {
        if (!preg_match_all(
            '~' . self::VARIABLE_REGEX . '~x', $route, $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        )) {
            return [$route];
        }

        $offset = 0;
        $routeData = [];
        foreach ($matches as $set) {
            if ($set[0][1] > $offset) {
                $routeData[] = substr($route, $offset, $set[0][1] - $offset);
            }
            $routeData[] = [
                $set[1][0],
                isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX
            ];
            $offset = $set[0][1] + strlen($set[0][0]);
        }

        if ($offset !== strlen($route)) {
            $routeData[] = substr($route, $offset);
        }

        return $routeData;
    }
}
