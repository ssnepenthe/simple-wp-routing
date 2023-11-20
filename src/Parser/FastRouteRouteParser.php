<?php

declare(strict_types=1);

namespace SimpleWpRouting\Parser;

use SimpleWpRouting\Exception\BadRouteException;

/**
 * Adapted from https://github.com/nikic/FastRoute/blob/181d480e08d9476e61381e04a71b34dc0432e812/src/RouteParser/Std.php
 */
final class FastRouteRouteParser implements RouteParserInterface
{
    private const DEFAULT_DISPATCH_REGEX = '[^/]+';

    private const VARIABLE_REGEX = <<<'REGEX'
    \{
        \s* ([a-zA-Z_][a-zA-Z0-9_-]*) \s*
        (?:
            : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
        )?
    \}
    REGEX;

    /**
     * @return array<string, array<string, string>>
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

        foreach ($segments as $n => $segment) {
            if ('' === $segment && 0 !== $n) {
                throw new BadRouteException('Empty optional part');
            }

            $currentRoute .= $segment;

            [$regex, $queryArray] = $this->parsePlaceholders($currentRoute);

            $rewrites["^{$regex}$"] = $queryArray;
        }

        return $rewrites;
    }

    /**
     * Parses a route string that does not contain optional segments.
     *
     * @param string $route
     * @return array{0: string, 1: array<string, string>}
     */
    private function parsePlaceholders($route)
    {
        if ('' === $route) {
            throw new BadRouteException('Empty routes not allowed');
        }

        if (! preg_match_all(
            '~' . self::VARIABLE_REGEX . '~x',
            $route,
            $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        )) {
            return [$route, []];
        }

        $offset = 0;
        $regex = '';
        $queryArray = [];

        foreach ($matches as $n => $set) {
            if ($set[0][1] > $offset) {
                $regex .= substr($route, $offset, $set[0][1] - $offset);
            }

            $regex .= '(' . (isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX) . ')';
            $queryArray[$set[1][0]] = '$matches[' . ($n + 1) . ']';
            $offset = $set[0][1] + strlen($set[0][0]);
        }

        if ($offset !== strlen($route)) {
            $regex .= substr($route, $offset);
        }

        return [$regex, $queryArray];
    }
}
